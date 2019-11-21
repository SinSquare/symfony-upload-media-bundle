# Translation Bundle

[![pipeline status](https://gitlab.com/katona.abel/symfony-upload-media-bundle/badges/master/pipeline.svg)](https://gitlab.com/katona.abel/symfony-upload-media-bundle/commits/master)
[![coverage report](https://gitlab.com/katona.abel/symfony-upload-media-bundle/badges/master/coverage.svg)](https://gitlab.com/katona.abel/symfony-upload-media-bundle/commits/master)


**Symfony bundle for managing file uploads, with chunked file upload**

The bundle also contains the blueimp/jQuery-File-Upload and jQuery assets.

## Install

Install this bundle via Composer:

``` bash
$ composer require subbeta/upload-media-bundle
```

First, register the bundle:

```php
# config/bundles.php
return [
    // ...
    UploadMediaBundle\UploadMediaBundle::class => ['all' => true],
];
```

Add the routes:

```yaml
# config/routes/upload_media.yaml
_media:
    resource: '@UploadMediaBundle/Resources/config/routes.yaml'
    #prefix:  /upload
```

Install the assets:

```bash
$ php bin/console assets:install
```

## Create a form

```php

use Symfony\Component\Form\AbstractType;
use UploadMediaBundle\Form\UploadMediaType;

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'image',
                UploadMediaType::class,
                array(
                    'label' => 'image',
                    // the following options can be set
                    //'multiple' => false,
                    //'additiona_data' => array("data" => 'data')   
                    //'data_class' => UploadedMedia::class,
            		//'accept' => 'image/*',
            		//'upload_path_name' => 'upload_media_ajax',  
                )
            )
        ;

        // ... 
    }
    
    // ... 
}

```

## Create a template with fileupload code

```twig

{# form rendering #}

<script src="{{ asset('bundles/uploadmedia/blueimp/js/vendor/jquery.ui.widget.js') }}"></script>
<script src="{{ asset('bundles/uploadmedia/blueimp/js/jquery.iframe-transport.js') }}"></script>
<script src="{{ asset('bundles/uploadmedia/blueimp/js/jquery.fileupload.js') }}"></script>

<script type="text/javascript">
$(document).ready(function() {
    
    $('#{{ form.image.vars.id }}_container input.fileupload').fileupload({
        url: $(this).data("url"),
        dataType: 'json',
        limitConcurrentUploads: 3,
        sequentialUploads: false,
        maxChunkSize: 1000000, // 1 MB
        submit: function (e, data) {
            var additional_data = $(this).data("additionaldata");
            data.formData = {};
            if(typeof additional_data === 'string') {
                data.formData["additionalData"] = data;
            } else {
                for (key in additional_data) {
                    var nk = "additionalData["+key+"]";
                    data.formData[nk] = additional_data[key];
                }
            }
        },
        start: function (e, data) {
            var container = $(this).closest('.fileupload-container');
            container.find('.progress-bar').css('width','0%').text("0%");
        },
        done: function (e, data) {
            $container = $(this).closest('.upload_media_container');
            $input = $container.find('input.upload_result');
            
            var obj = [];
            try {
                obj = JSON.parse($input.val());
            } catch(e) {
            }

            for (var i = 0; i < data.result.data.length; i++) {
                obj.push(data.result.data[i]);
            }            

            if ($input.attr("multiple") == null) {
                obj = [obj.pop()];
            }

            $input.val(JSON.stringify(obj));
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            var container = $(this).closest('.fileupload-container');
            container.find('.progress-bar').css('width',progress + '%').text(progress + '%');
        },
    });
});
</script>

```

## Getting the upload files from the form

```php

public function uploadAction(Request $request): Response
{
	// ...

	$form->handleRequest($request);
	$images = $form->get('image')->getData();
	// images is a single UploadedMedia object, or an array of UploadedMedia objects in case of multiple => true

	// ...
}

```

## Events

* **UploadMediaEvents::GETFILES**  
	The GETFILES event occurs at the beginning of the upload process.  
	This event is for extracting the upload files from the request.  
	By default it extracts all files.  
	@Event("UploadMediaBundle\Events\GetUploadedFilesEvent")  

* **UploadMediaEvents::KEEPFILE**  
	The KEEPFILE event occurs before the uploaded file is moved.  
	This event allows you to decide if the file should be moved(kept) or not.  
	If it is not moved, the file will be deleted after the script executed.  
	@Event("UploadMediaBundle\Events\KeepfileEvent")  

* **UploadMediaEvents::UPLOAD**  
	The UPLOAD event occurs after the file is uploaded.  
	This event allows you to move, modify the uploaded file.  
	@Event("UploadMediaBundle\Events\UploadedEvent")  

* **UploadMediaEvents::FILEDATA**  
	The FILEDATA event occurs after the file is moved, and before the response is created.  
	This event allows you to modify the data that will be sent back in an array form.  
	@Event("UploadMediaBundle\Events\GetFileDataEvent")  

* **UploadMediaEvents::CHUNKDATA**  
	The CHUNKDATA event occurs after the chunk is uploaded, and before the response is created.  
	This event allows you to modify the data that will be sent back in an array form.  
	@Event("UploadMediaBundle\Events\GetChunkDataEvent")  

* **UploadMediaEvents::RESPONSE**  
	The RESPONSE event occurs before the response is sent back.  
	This event allows you modify the response.  
	@Event("UploadMediaBundle\Events\GetResponseEvent")  
  
## Custom data class

You might want to create a custom data class if you want to get custom data from the uploaded files. To get it work you need to create a custom data class, set it in the form, and optionally modify the response using the UploadMediaEvents::FILEDATA or UploadMediaEvents::RESPONSE events.

Create the data class

The class MUST implement the UploadedMediaInterface!

```php

use UploadMediaBundle\Contract\UploadedMediaInterface;

class MyDataClass implements UploadedMediaInterface
{
    // ... 
}

```

Set the data_class of the form


```php

class UploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	// ... 
            ->add(
                'image',
                UploadMediaType::class,
                array(
                    'label' => 'image',
                    'data_class' => MyDataClass::class, 
                    // ... 
                )
    // ... 

```

Modify the returned data

```php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use UploadMediaBundle\Event\GetFileDataEvent;
use UploadMediaBundle\Event\UploadMediaEvents;

class MediaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            UploadMediaEvents::FILEDATA => [
                ['getMediaResponse', 0],
            ],
        ];
    }

    public function getMediaResponse(GetFileDataEvent $event)
    {
    	// ... 
    	$event->setData($data);
    }
}

```