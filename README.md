# About snippets folder

This repo was created to help developers, particularly in genealogy.

## ConvertToWebp.php

Convert all files PNG, JPG, JPEG and BMP from directory to WEBP format.
All converted images are saved under the old name and extension + the new extension (ex: image.png -> image.png.webp).

```php
$files = scandir("./"); // scan and convert is the current directory
