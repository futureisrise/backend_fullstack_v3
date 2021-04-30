# Codeigniter3 - a SonicIgniter Framework 🔥

![img.png](sonic.gif)

# SuperFAST Easy And Lightweight PHP Framework

We just make better and faster shit, rather than comminity :) Maybe we should call it SonicIgniter ? :)

# REQUIRMENTS

AT LEAST PHP7.3

![](https://media1.tenor.com/images/64e1b9b4745048fb2d51d23f241298ee/tenor.gif?itemid=10523126)

# GUIDES - How to and examples

### [Wiki - HOW TO WORK WITH, STYLE / CODE GUIDE. Детальный разбор работы фреймворка](docs/Code_style_and_guide.md)

### [Submodule framework install guide](docs/Submodule.md)

### [Database - Sparrow guide](docs/Sparrow.md)

### Igniter

[Igniter - cli guide](docs/Igniter.md)

Like in laravel, we have our igniter - alias for CLI.

Allows to use framework defined functions such as `php igniter system mysql_analyze user` or `php igniter system generate_emerald_model user My_Model`

### Loader extending

If you want to use subdomain by one application folder, define

`define('PROJECT', isset($_SERVER['CI_PROJECT']) ? $_SERVER['CI_PROJECT'] : 'main');`

`define('PROJECT_CONFIG_PATH', PROJECT . '/');`

and put `config/global` folder to the project. ENVIRONMENT is supported everywhere!

### Redis driver for asynchronous requests

We fixed redis session driver for ajax requests, and now it's using non blocking mechanism! Take care about transactions and locks!

### Env check

We added `is_dev` `is_prod` `is_testing` function :)

### SPA SUPPORT

We added `IS_SPA_APPLICATION`  constant checking for using method `->response_view()` to send information in JSON to Frontend.

### Logger Updates

Added placeholders, for faster debug. Core rewritten fully.

https://codeigniter.com/user_guide/general/logging.html?#modifying-the-message-with-context

```
Placeholder	Inserted value
{post_vars}	$_POST variables
{get_vars}	$_GET variables
{session_vars}	$_SESSION variables
{env}	Current environment name, i.e., development
{file}	The name of file calling the logger
{line}	The line in {file} where the logger was called
{env:foo}	The value of ‘foo’ in $_ENV
```

### RESPONSE HELP FUNCTIONS

We added `response()` `response_success()` `response_error()` and same for CLI. `cli_response()` and etc Functions, to feel better when return some data.

### String features

`contains()`, `isJSON()` helps to work with strings :)

### Emerald_model

Next generation model for CI !

Firstly you should Init Sparrow DB library from file: Library/Sparrow_starter.php . Easiest way to add to autoload.

### Emerald_enum 

Easy to work with enums, no need to use reflection class instead to get all class constants 
