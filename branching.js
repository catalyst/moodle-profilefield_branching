M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parent1id, desired1, parent2id, desired2) {

    // Hides a dependant field and sets all it's data to @
    function hide(fieldid) {
        Y.all(fieldid + ' input').set('value', '@');
        var groupid = fieldid.replace('fitem_', 'fgroup_') + '_parent';
        var fieldclass = fieldid.replace('#fitem_', '.');

        if (Y.one(fieldid + ' select')) {
            Y.one(fieldid + ' select').prepend('<option value="@">@</option>');
            Y.one(fieldid + ' select').set("selectedIndex", 0);
            YUI().use('node-event-simulate', function(Y) {
                Y.one(fieldid + ' select').simulate("change");
            });
        }

        Y.all(fieldid).setStyle('display', 'none');
        Y.all(groupid).setStyle('display', 'none');
        Y.all(fieldclass).setStyle('display', 'none');
    }

    // Hides a dependant field and sets all it's data to empty
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
            YUI().use('node-event-simulate', function(Y) {
                Y.one(fieldid + ' select').simulate("change");
            });
        }
    }

    /*
     * Detects wether a field contains the desired value
     * and handles te various types of parent fields.
     */
    function isDesired(parentid, desired) {

        // Test for selects
        var select = Y.one('[name=profile_field_'+parentid+']');
        if (select){
            return select.get('value') == desired;
        }

        // Test for matrix checkboxes
        var checkgroup = Y.one('#fgroup_id_profile_field_' + parentid + '_grp');
        if (checkgroup){
            return Y.one('#fgroup_id_profile_field_' + parentid + '_grp').all('input')._nodes[1*desired].checked;
        }

        return false;
    }

    /*
     * Determine if this fields dependancies are met, and if so hide or show.
     */
    function check(){

        if (isDesired(parent1id, desired1) && (!parent2id || isDesired(parent2id, desired2) ) ) {
            show(fieldid);
        } else {
            hide(fieldid);
        }
    }

    /*
     * detect the field type and add appropriate handlers
     */
    function setupOnChange(parentid){
        var select = Y.one('[name=profile_field_'+parentid+']');
        if (select){
            select.on('change', check);
        }

        var checkgroup = Y.one('#fgroup_id_profile_field_' + parentid + '_grp');
        if (checkgroup){
            Y.one('#fgroup_id_profile_field_' + parentid + '_grp').all('input').on('change', check);
        }

    }

    setupOnChange(parent1id);

    if (parent2id) {
        setupOnChange(parent2id);
    }

    // Check onload. Setup last as may trigger recursive change events
    check();

};

M.profile_field_branching_options = {};

M.profile_field_branching_options.init = function(Y, parentid, fieldid, isoptional) {

    var pid = parentid.replace('fitem_','');
    var fid = fieldid.replace('fitem_','');

    function populate(parentid, fieldid) {
        var parentname = Y.one(parentid).one('select').get('value');
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);

                var sel = Y.one(fieldid).one('> .fselect').one(fid);

                // First save what was there
                var previous = sel.get('value');

                // Remove all existing items.
                sel.get('childNodes').remove();

                // If optional add a blank first entry
                if (isoptional){
                    sel.append('<option value="">none</option>');
                }
                var i = 0;
                for (item in response[0]) {
                    var val = response[0][item];
                    sel.append('<option value="' + i + '">' + val + '</option>');
                    i++;
                }
                sel.set('value', previous);
            }
        };
        var field = {
            parentname: parentname
        }

        xhr.open('POST', M.cfg.wwwroot + '/user/profile/field/branching/ajax.php', true);
        xhr.send(JSON.stringify(field));
    }

    // Run at page load.
    populate(parentid, fieldid);

    // Run on change.
    Y.one(parentid).on('change', function(e) {
        populate(parentid, fieldid);
    });
};
