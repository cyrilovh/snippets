# About snippets folder

This repo was created to help developers, particularly in genealogy.

## ConvertToWebp.php

Convert all files PNG, JPG, JPEG and BMP from directory to WEBP format.
All converted images are saved under the old name and extension + the new extension (ex: image.png -> image.png.webp).

```php
$files = scandir("./"); // scan and convert is the current directory
```

## form.class.php

Class for generate from and checkout it after user submit ("light version").
Example for create a form:

```php
    $formLogin = new form(array( // i declare my new object
        "method" => "post", // i give the method attr
        "action" => "", // i give action attr
        "class"=>"login", // i give className ou className list (not required)
    ));

    $formLogin->setElement("input", array( // here i give the type of tag
        "type" => "text", // i give the type of input
        "placeholder" => "Nom d&apos;utilisateur", // i set a placeholder
        "name" => "username", // i give a className
        "required" => "required", // i add the attr required
        "minlength" => 4,
        "maxlength" => 25
    ));

    $formLogin->setElement("input", array(
        "type" => "password",
        "placeholder" => "Mot de passe",
        "name" => "password",
        "minlength" => 3,
        "maxlength" => 25
    ));

    $formLogin->setElement("input", array(
        "type" => "color",
        "placeholder" => "#fffff",
        "name" => "color",
    ));

    $formLogin->setElement("input", array(
        "type" => "submit",
        "value" => "Connexion",
        "name" => "submit",
        "class" => "btn btn-primary" // i add a class to the element
    ));
```

For check if the form is correctly filled:
```php
if(count($formLogin->check())==0){
  // continue..
}
```

More informations about this class on the documentation of the project "geneager" (https://github.com/cyrilovh/geneager/).
Warning: the documentation may be change.
