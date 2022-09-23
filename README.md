# 

![github actions](https://github.com/prugala/symfony-request-dto/workflows/Tests/badge.svg)
[![Latest Stable Version](http://poser.pugx.org/prugala/symfony-request-dto/v)](https://packagist.org/packages/prugala/symfony-request-dto)
[![Total Downloads](http://poser.pugx.org/prugala/symfony-request-dto/downloads)](https://packagist.org/packages/prugala/symfony-request-dto)
[![License](http://poser.pugx.org/prugala/symfony-request-dto/license)](https://packagist.org/packages/prugala/symfony-request-dto)
[![PHP Version Require](http://poser.pugx.org/prugala/symfony-request-dto/require/php)](https://packagist.org/packages/prugala/symfony-request-dto)

Map request on your DTO object with zero configuration.
## Install

```shell
composer require prugala/symfony-request-dto
```

## Support

- Content data
- Form-data
- Query parameters 
- Uploaded files
- Headers

#### TODO
- Configurable normalizers and encoders

## Usage

1. Create a DTO that implements the interface `Prugala\RequestDto\Dto\RequestDtoInterface`
2. Use your DTO in a Controller e.g.:
    ```php 
    <?php
   declare(strict_types=1);
   
   namespace App\Controller;
   
   use Symfony\Component\HttpFoundation\JsonResponse;
   use App\Dto\ExampleDto;
   
   class ExampleController
   {
        public function update(ExampleDto $dto): JsonResponse
        {
            return new JsonResponse($dto);
        }
   }
    ```
5. Done, your JSON (other data are on TODO list) will be mapped on your object

### Support for uploaded files
Bundle has support for uploaded files.
```php
    #[Assert\File(maxSize: 1000, mimeTypes: 'text/plain')]
    public ?UploadedFile $exampleFile = null;
```

Send request with form-data with field `exampleFile` and you will have access to your file in object property.

### Validation
You can use symfony/validator to validate request.  
If you provide invalid data you will get response 400 with json object with violation list.  

Example:
1. Create DTO with constraint:
   ```php 
    <?php
    declare(strict_types=1);

    namespace App\Dto;

    use Prugala\RequestDto\Dto\RequestDtoInterface;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class ExampleDto implements RequestDtoInterface
    {
        public string $name;

        #[Assert\Range(min: 2, max: 10)]
        public int $position;
    }
   ```
2. Call your action with JSON object:
    ```json
    {
      "name": "test",
      "position": 1 
   }
    ```
3. You get response 400 with JSON:
    ```json 
   {
        "errors": [
            {
                "message": "This value should be between 2 and 10.",
                "code": "04b91c99-a946-4221-afc5-e65ebac401eb",
                "context": {
                    "field": "position"
                }
            }
        ]
   }
    ```

If you want to change response format, overwrite method `formatErrors` in listener `Prugala\RequestDto\EventListener\RequestValidationExceptionListener`

## Testing

```shell
composer tests
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
