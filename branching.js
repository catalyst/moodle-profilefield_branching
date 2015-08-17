M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parentid, desired, itemname) {

    // Have to pass them in here, otherwise if there are multiple on the page it only works for the last one.
    this.checkQual = function(fieldid, parentid, itemname) {
        itemname = itemname.replace(/\s/g, '');
        parentname = parentid + "_" + itemname;

        function hide(fieldid) {
            Y.one(fieldid).setStyle('display', 'none');
            Y.one(fieldid).set('value', "@");
        }
        function show(fieldid) {
            Y.one(fieldid).setStyle('display', '');
            Y.one(fieldid).set('value', "");
        }


        if (Y.one(parentname)) {
            // Hide the field if we dont have the required value.
            if (Y.one(parentname).get('checked') != true) {
                hide(fieldid);
            } else {
                // We need this since we now have 2 possible change events.
                show(fieldid);
            }

            Y.one(parentname).on('change', function(e) {
                // The double check on state is not ideal, but it is required to fix a weird corner case
                // bug where if it is already checked and changed from Vic to something else it updates.
                if (this.get('checked') && Y.one('#id_profile_field_vettrakrstate').get('value') == 2) {
                    show(fieldid);
                } else {
                    hide(fieldid);
                }
            });
        }
    };

    function hide(fieldid) {
        Y.all(fieldid + ' input').set('value', '@');
        var groupid = fieldid.replace('fitem_', 'fgroup_') + '_parent';
        Y.all(fieldid).setStyle('display', 'none');
        Y.all(groupid).setStyle('display', 'none');
        // If there is a text element, hide that to.
        var fieldclass = fieldid.replace('#fitem_', '.');
        Y.all(fieldclass).setStyle('display', 'none');
    }
    function show(fieldid) {
        var groupid = fieldid.replace('fitem_', 'fgroup_') + '_parent';
        Y.all(fieldid).setStyle('display', '');
        Y.all(groupid).setStyle('display', '');
        Y.all(fieldid + ' input').set('value', '');
        Y.all(fieldid).set('placeholder', "empty");
        var fieldclass = fieldid.replace('#fitem_', '.');
        Y.all(fieldclass).setStyle('display', '');
        Y.all(fieldclass).set('value', '');
    }

    if (typeof itemname === 'undefined') { // Text, menu or declaration.
        if (Y.one(parentid)) {
            // Hide the field if we dont have the required value.
            if (Y.one(parentid).get('value') != desired) {
                hide(fieldid);
            }

            Y.one(parentid).on('change', function(e) {
                if (this.get('value') == desired) {
                    show(fieldid);
                } else {
                    hide(fieldid);
                }
            });
        }
    } else { // Qual.
        if (Y.one('#id_profile_field_vettrakrstate').get('value') == 2) {
            M.profile_field_branching.checkQual(fieldid, parentid, itemname);
        } else { // It's not the required state, so do nothing.
            Y.one(fieldid).setStyle('display', 'none');
        }

        Y.one('#id_profile_field_vettrakrstate').on('change', function(e) {
            if (this.get('value') == 2) {
                M.profile_field_branching.checkQual(fieldid, parentid, itemname);
            } else { // It's not the required state, so do nothing.
                Y.one(fieldid).setStyle('display', 'none');
            }
        });
    }
};

M.profile_field_branching_options = {};

M.profile_field_branching_options.init = function(Y, fieldid, parentid, defaultid) {

    var fid = fieldid.replace('fitem_','');
    var pid = parentid.replace('fitem_','');
    var did = defaultid.replace('fitem_','');

    function populate(shortname, fieldid, parentid) {
        var name =  Y.one('#id_shortname').get('value');
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);

                // Remove all existing items.
                Y.one(fieldid).one('> .fselect').one(fid).get('childNodes').remove();

                var i = 1;
                for (item in response[0]) {
                    var val = response[0][item];
                    if (val == 'checked') {
                        Y.one(fieldid).one('> .fselect').one(fid).append('<option value="' + item.trim() + '">' + val + '</option>');
                    } else {
                        Y.one(fieldid).one('> .fselect').one(fid).append('<option value="' + i + '">' + val + '</option>');
                    }
                    i++;
                }
                // Set the default if its there.
                Y.one(fieldid).one('> .fselect').one(fid).set('value', response[2]);
                // This is only for the multicheckbox qualification type.
                // Remove all existing items.
                if (Y.one(defaultid).one('> .fselect') ){
                    Y.one(defaultid).one('> .fselect').one(did).get('childNodes').remove();
                    for (item in response[1]) {
                            Y.one(defaultid).one('> .fselect').one(did).append('<option value="' + item.trim() + '">' + item + '</option>');
                    }
                    // Set the default if its there.
                    Y.one(defaultid).one('> .fselect').one(did).set('value', response[3]);
                }
            }
        };
        var field = {
            name: name,
            shortname: shortname
        }

        xhr.open('POST', M.cfg.wwwroot + '/user/profile/field/branching/ajax.php', true);
        xhr.send(JSON.stringify(field));
    }

    // Run at page load.
    populate(Y.one(parentid).one('> .fselect').one(pid).get('value'), fieldid, parentid);

    // Run on change.
    Y.one(parentid).on('change', function(e) {
        var shortname = this.one('> .fselect').one(pid).get('value');
        populate(shortname, fieldid, parentid);
    });
};
