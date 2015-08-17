M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parentid, desired, itemname) {

    function hide(fieldid) {
        Y.all(fieldid + ' input').set('value', '@');
        var groupid = fieldid.replace('fitem_', 'fgroup_') + '_parent';
        var fieldclass = fieldid.replace('#fitem_', '.');

        if (Y.one(fieldid + ' select')) {
            Y.one(fieldid + ' select').prepend('<option value="@">@</option>');
            Y.one(fieldid + ' select').set("selectedIndex", 0);
        }

        Y.all(fieldid).setStyle('display', 'none');
        Y.all(groupid).setStyle('display', 'none');
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

        if (Y.one(fieldid + ' select')) {
            Y.all(fieldid + ' option[value=@]').remove();
        }
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

        var groupid = parentid.replace('#', '#fgroup_')+'_grp';

        var checkbox = Y.one(Y.all(parentid.replace('#', '#fgroup_')+'_grp input[value]')._nodes[itemname]);

        // IF the vettrak state is 2:Vic AND our tickbox is selected then show, otherwise hide
        function check(){
            var isVic = Y.one('#id_profile_field_vettrakrstate') && Y.one('#id_profile_field_vettrakrstate').get('value') == 2;
            if (isVic && checkbox.get('checked')) {
                show(fieldid);
            } else {
                hide(fieldid);
            }

        }


        if (Y.one('#id_profile_field_vettrakrstate')) {
            Y.one('#id_profile_field_vettrakrstate').on('change', check );
        }
        if (checkbox) {
            checkbox.on('change', check);
        }
        check();
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
                        var val = response[1][item];
                            Y.one(defaultid).one('> .fselect').one(did).append('<option value="' + item.trim() + '">' + val + '</option>');
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
