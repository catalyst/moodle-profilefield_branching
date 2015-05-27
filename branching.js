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
                if (this.get('checked') && Y.one('#id_profile_field_vettrakrstate').get('value') == 1) {
                    Y.one(fieldid).setStyle('display', '');
                    Y.one(fieldid).set('value', "");
                } else {
                    Y.one(fieldid).setStyle('display', 'none');
                }
            });
        }
    };

    if (typeof itemname === 'undefined') { // Text or menu.
        if (Y.one(parentid)) {
            // Hide the field if we dont have the required value.
            if (Y.one(parentid).get('value') != desired) {
                Y.one(fieldid).setStyle('display', 'none');
            }

            Y.one(parentid).on('change', function(e) {
                if (this.get('value') == desired) {
                    Y.one(fieldid).setStyle('display', '');
                    Y.one(fieldid).set('value', "");
                } else {
                    Y.one(fieldid).setStyle('display', 'none');
                }
            });
        }
    } else { // Qual.
        if (Y.one('#id_profile_field_vettrakrstate').get('value') == 1) {
            M.profile_field_branching.checkQual(fieldid, parentid, itemname);
        } else { // It's not the required state, so do nothing.
            Y.one(fieldid).setStyle('display', 'none');
        }

        Y.one('#id_profile_field_vettrakrstate').on('change', function(e) {
            if (this.get('value') == 1) {
                M.profile_field_branching.checkQual(fieldid, parentid, itemname);
            } else { // It's not the required state, so do nothing.
                Y.one(fieldid).setStyle('display', 'none');
            }
        });
    }
};
