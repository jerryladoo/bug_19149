This is for reproducing bug #19149
========================

The default values given in Configuration.php are set correctly in XxxxxExtension.php, but in compiler pass they are 
ignored and always null. This little project will reproduce this bug.
https://github.com/symfony/symfony/issues/19149

What's inside?
--------------

* Symfony Standard Edition 2.7
* Composer
* A simple bundle to reproduce this bug: ReproducingBundle, which contains:
    * A simple command called `try:me`
    * a simple config file `bug19149.yml`, which contains two nodes, `working_dir` and `use` with default values, `/tmp` and `ZipArchive` respectively

    ```
    // default
    bug19149_reproducing:
        archiver:
            zip:
                source: destination.local
                destination: local
                lib:
                    command:
                      standard: 'zip -r %%s %%s'
    
    // or you can change to this for seeing different result
    bug19149_reproducing:
        archiver:
            zip:
                source: destination.local
                destination: local
                working_dir: ~    // this is the field with default value
                use: ~        // same as above
                lib:
                    command:
                      standard: 'zip -r %%s %%s'
    ```

How to reproduce it
--------------
* `git clone` this project
* run `composer install` and keep everything default
* run `php app/console try:me`, then you will see that config settings are outputted on the console
* in case if you want to see it again, pls delete `cache/dev` folder and run the command above again.


The output you will see
--------------
```
Bug19149\ReproducingBundle\DependencyInjection\Bug19149ReproducingExtension: 
  {"archiver":{"zip":{"source":"destination.local","destination":"local","lib":{"command":{"standard":"zip -r %%s %%s"}},"working_dir":"\/tmp","use":"ZipArchive"}}}

Bug19149\ReproducingBundle\DependencyInjection\Compiler\MyPass: 
  [{"archiver":{"zip":{"source":"destination.local","destination":"local","lib":{"command":{"standard":"zip -r %%s %%s"}}}}}]
  
```

Solution
--------------
According to the response from Symfony member, this is an expected behaviour as `getExtensionConfig` in compiler pass returns only unprocessed config while $config in extension is a processed config.

The solution recommended is to set your config into container in your extension, and then get it out in compiler pass like so
```
// in your XxxxxExtension.php 
$container->setParameter("config", $config); 
```

```
// in your XxxxxCompilerPass.php 
$container->getParameter("config");
```
