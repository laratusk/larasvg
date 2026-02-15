# Background

## Setting Background Color

LaraSVG supports HEX and RGB color formats:

```php
// HEX color
$converter->setBackground('#ffffff');
$converter->setBackground('#ff007f');

// Short HEX
$converter->setBackground('#fff');

// RGB color
$converter->setBackground('rgb(255,0,0)');
$converter->setBackground('rgb(0, 128, 255)');
```

## Background Opacity

Control the background transparency with a float value between `0.0` (fully transparent) and `1.0` (fully opaque):

```php
$converter->setBackgroundOpacity(0.5);
```

## Combined Example

```php
SvgConverter::open(resource_path('svg/logo.svg'))
    ->setFormat('png')
    ->setDimensions(512, 512)
    ->setBackground('#ffffff')
    ->setBackgroundOpacity(1.0)
    ->toFile(storage_path('app/logo-white-bg.png'));
```

## Validation

- Invalid color formats throw a `SvgConverterException`
- Opacity values outside `0.0`–`1.0` throw a `SvgConverterException`

```php
// Throws SvgConverterException
$converter->setBackground('not-a-color');

// Throws SvgConverterException
$converter->setBackgroundOpacity(1.5);
```
