function checkAll(form, all, name)
{
	$(form).getElements('input[name^=' + name + ']').setProperty('checked', $(all).checked);
}