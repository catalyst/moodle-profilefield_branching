M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parentid, desired) {
    parentelement = Y.one(parentid);
    if (parentelement) {
        target = Y.one(fieldid);
        // Hide the field if we dont have the required value.
        if (parentelement.get('value') != desired) {
            target.setStyle('display', 'none');
        }

        setTimeout(function() {
            parentelement.on('change', function(e) {
                if (this.get('value') == desired) {
                    target.setStyle('display', '');
                    target.set('value', "");
                } else {
                    target.setStyle('display', 'none');
                }
            });
        }, 200);
    }
};
