M.profile_field_branching = {};

M.profile_field_branching.init = function(Y, fieldid, parent1id, desired1, parent2id, desired2) {

    var logging = M.cfg.developerdebug;

    // Hides a dependant field and sets all it's data to an empty string
    function hide(fieldid) {
        Y.all(fieldid + ' input').set('value', '');
        var groupid = fieldid.replace('fitem_', 'fgroup_') + '_parent';
        var fieldclass = fieldid.replace('#fitem_', '.');

        if (Y.one(fieldid + ' select')) {
            Y.one(fieldid + ' select').prepend('<option value="0"></option>');
            Y.one(fieldid + ' select').set("selectedIndex", 0);
        }

        // TODO checkbox group logic here

        Y.all(fieldid).setStyle('display', 'none');
        Y.all(groupid).setStyle('display', 'none');
        Y.all(fieldclass).setStyle('display', 'none');
    }

    // Hides a dependant field and sets all it's data to empty
    function show(fieldid) {
        var groupid = fieldid.replace('fitem_', 'fgroup_') + '_parent';
        Y.all(fieldid).setStyle('display', '');
        Y.all(groupid).setStyle('display', '');
        Y.all(fieldid).set('placeholder', "empty");
        var fieldclass = fieldid.replace('#fitem_', '.');
        Y.all(fieldclass).setStyle('display', '');
        var input2 = Y.one(fieldid + ' input');

        if (Y.one(fieldid + ' select')) {
            Y.all(fieldid + ' option[value="0"]').remove();
        }
        // TODO add checkbox group logic here
    }

    /*
     * Detects wether a field contains the desired value
     * and handles te various types of parent fields.
     */
    function isDesired(parentid, desired) {

        if (parentid == "0"){
            return false;
        }

        // Test for selects
        var select = Y.one('[name=profile_field_'+parentid+']');
        if (select){
            return select.get('options').item(select.get('selectedIndex')).get('text') == desired;
        }

        // Test for locked fields
        var locked = Y.one('[id=fitem_id_profile_field_'+parentid+'] [class=felement fstatic]').getContent()
        if (locked) {
            return locked == desired;
        }

        // Test for matrix checkboxes
        var checkgroup = Y.one('#fgroup_id_profile_field_' + parentid + '_grp');
        if (checkgroup){
            var check = checkgroup.one("input[data-name='" + desired + "']");
            if (check) {
                return check.get('checked');
            } else {
                if (logging) {
                    window.console.log('Error: ' + parentid + ' : ' + desired);
                }
            }
        }

        return false;
    }

    /*
     * Determine if this fields dependancies are met, and if so hide or show.
     */
    function check(){
        if (logging){
            window.console.log(parent1id);
            window.console.log(desired1);
            window.console.log(parent2id);
            window.console.log(desired2);
        }
        if ((!parent1id || parent1id == "0")  || isDesired(parent1id, desired1)) {
            if ((!parent2id || parent2id == '0' ) || isDesired(parent2id, desired2) ) {
                show(fieldid);
                if (logging){
                    window.console.log('Showing '+fieldid + ' because ' + parent1id + ' = ' + desired1);
                }
            } else {
                hide(fieldid);
                if (logging){
                    window.console.log('Hiding2 '+fieldid + ' because ' + parent1id + ' != ' + desired1);
                }
            }
        } else {
            hide(fieldid);
            if (logging){
                window.console.log('Hiding '+fieldid + ' because ' + parent1id + ' != ' + desired1);
            }
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
                    sel.append('<option value="' + val + '">' + val + '</option>');
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
