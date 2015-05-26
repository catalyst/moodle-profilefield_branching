M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parentid, desired) {
    if (Y.one(parentid)) {
        target = Y.one(fieldid);
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
};
