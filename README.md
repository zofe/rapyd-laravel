## Fork from https://github.com/zofe/rapyd-laravel

## the ‘dev’ branch is newest code, master branch keeps the same as upstream.

## dev branch only tested under Laravel 5.1.

## added features:

1. fix security issue on 'readonly' mode

2. add Datagrid::buildExcel() function （add maatwebsite/excel by composer）

3. Date组件默认的format改成'Y-m-d', lang改为'zh-CN'，方便兼容HTML5自带的日期组件。

4. select组件在默认Inline的时候,会填充一个默认的PlaceHolder
	```php
		//if ($this->type!='select') {
			$this->attributes["placeholder"] = $this->label;
		//}
	```