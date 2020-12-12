## Whats New? 

- You can use attachPivot field with pivot Fields.
- You can search elements by custom fields instead only name

# Nova Attach Pivot

##### Forked from Dillingham's REPO. Changed the name to avoid dependencies conflicts. I will be so grateful if someone can use this branch and finish the TODO list.

[![Latest Version on Github](https://img.shields.io/github/release/dillingham/nova-attach-many.svg?style=flat-square)](https://packagist.org/packages/dillingham/nova-attach-many)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/nova-attach-many.svg?style=flat-square)](https://packagist.org/packages/dillingham/nova-attach-many) [![Twitter Follow](https://img.shields.io/twitter/follow/dillinghammm?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/dillinghammm)

Belongs To Many create & edit form UI for Nova. Enables attaching relationships easily and includes validation.

![attach-pivot](https://user-images.githubusercontent.com/29180903/52160651-be7fd580-2687-11e9-9ece-27332b3ce6bf.png)

### Installation

```bash
composer require manojo123/nova-attach-pivot
```

### Usage

```php
use NovaAttachPivot\AttachPivot;
```
```php
public function fields(Request $request)
{
    return [
        AttachPivot::make('Permissions'),
    ];
}
```

### Validation

You can set min, max, size or custom rule objects

```php
->rules('min:5', 'max:10', 'size:10', new CustomRule)
```

<img src="https://user-images.githubusercontent.com/29180903/52160802-9ee9ac80-2689-11e9-9657-80e3c0d83b27.png" width="75%" />


### Options

Here are a few customization options

- `->showCounts()` Shows "selected/total"
- `->pivotFields(['qty'])` Adds pivot fields to be attached inline
- `->searchableFields(['bar_code'])` Search box can find by name plus all fields defined in this method
- `->showPreview()` Shows only selected
- `->hideToolbar()` Removes search & select all
- `->height('500px')` Set custom height
- `->fullWidth()` Set to full width
- `->help('<b>Tip:</b> help text')` Set the help text

### All Options Demo
<img src="https://user-images.githubusercontent.com/29180903/53781117-6978ee80-3ed5-11e9-8da4-d2f2408f1ffb.png" width="75%"/>

### Relatable
The attachable resources will be filtered by relatableQuery()
So you can filter which resources are able to be attached

### Authorization
This field also respects policies: ie Role / Permission
- RolePolicy: attachAnyPermission($user, $role)
- RolePolicy: attachPermission($user, $role, $permission)
- PermissionPolicy: viewAny($user)

### TODO

[] Add pagination for large amount of resources
[] Pass Nova Fields instead array of strings in pivotFields with full field framework customization. (Rules, Types, etc)
[] Refactorate code and perform some unit tests.
