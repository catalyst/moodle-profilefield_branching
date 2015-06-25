M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parentid, desired, itemname) {

    // Have to pass them in here, otherwise if there are multiple on the page it only works for the last one.
    this.checkQual = function(fieldid, parentid, itemname) {
        itemname = itemname.replace(/\s/g, '');
        parentname = parentid + "_" + itemname;
        if (Y.one(parentname)) {
            // Hide the field if we dont have the required value.
            if (Y.one(parentname).get('checked') != true) {
                Y.one(fieldid).setStyle('display', 'none');
            } else {
                // We need this since we now have 2 possible change events.
                Y.one(fieldid).setStyle('display', '');
            }

            Y.one(parentname).on('change', function(e) {
                // The double check on state is not ideal, but it is required to fix a weird corner case
                // bug where if it is already checked and changed from Vic to something else it updates.
                if (this.get('checked') && Y.one('#id_profile_field_vettrakrstate').get('value') == 2) {
                    Y.one(fieldid).setStyle('display', '');
                    Y.one(fieldid).set('value', "");
                } else {
                    Y.one(fieldid).setStyle('display', 'none');
                }
            });
        }
    };

    if (typeof itemname === 'undefined') { // Text, menu or declaration.
        if (Y.one(parentid)) {
            // Hide the field if we dont have the required value.
            if (Y.one(parentid).get('value') != desired) {
                Y.one(fieldid).setStyle('display', 'none');
                // If there is a text element, hide that to.
                var fieldclass = fieldid.replace('#fitem_', '.');
                if (Y.one(fieldclass)) {
                    Y.one(fieldclass).setStyle('display', 'none');
                }
            }

            Y.one(parentid).on('change', function(e) {
                if (this.get('value') == desired) {
                    Y.one(fieldid).setStyle('display', '');
                    Y.one(fieldid).set('value', "");
                    var fieldclass = fieldid.replace('#fitem_', '.');
                    if (Y.one(fieldclass)) {
                        Y.one(fieldclass).setStyle('display', '');
                        Y.one(fieldclass).set('value', "");
                    }
                } else {
                    Y.one(fieldid).setStyle('display', 'none');
                    // If there is a text element, hide that to.
                    var fieldclass = fieldid.replace('#fitem_', '.');
                    if (Y.one(fieldclass)) {
                        Y.one(fieldclass).setStyle('display', 'none');
                    }
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

M.profile_field_branching_options.init = function(Y, fieldid, parentid) {

    Y.one(parentid).on('change', function(e) {
        var shortname = this.one('> .fselect').one('#id_param3').get('value');
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);

                // Remove all existing items.
                Y.one(fieldid).one('> .fselect').one('#id_param4').get('childNodes').remove();

                var i = 1;
                for (item of response) {
                    Y.one(fieldid).one('> .fselect').one('#id_param4').append('<option value="' + i + '">' + item + '</option>');
                    i++;
                }
            }
        };
        xhr.open('POST', M.cfg.wwwroot + '/user/profile/field/branching/ajax.php', true);
        xhr.send(JSON.stringify(shortname));
    });
};
