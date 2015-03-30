# The task
Write a console command tool that accepts twitter account name (for instance Secretsales) and outputs keyword frequency for the past 100 tweets from a predefined account, most frequent on top, in the following format: 
- keyword1,count 
- keyword2,count 
- keyword3,count 

General notes: the goal of the task is to show your understanding of the modern coding standards and practices. Our focus is not only on solving the problem, but the elegancy of the solution. 

# Prerequisites
The following tools/software/servers should be already installed and properly configured:
- Apache
- PHP
- Git
- Composer
- PHPUnit

# How to install the project
-   clone the project from the Github into your root directory:
    ```sh
    git clone https://github.com/multeek/taskTweetsKeywordsCount.git
    ```
2. Navigate into your project directory
   ```sh
   cd {PATH_WHERE_THE_PROJECT_HAS_BEEN_CLONED}/taskTweetsKeywordsCount
   ```
3 Install the dependecies with the composer:
```sh
composer install
```

# How to run/test the task/project:
1. Navigate into your project directory
```sh
cd {PATH_WHERE_THE_PROJECT_HAS_BEEN_CLONED}/taskTweetsKeywordsCount
```
2. Run the console command:
```sh
php app/console twitter:count-keywords Secretsales
```
by default, the limit value of the parsed tweets is 100. You can count the keywords against a custom number of tweets using the --limit option:
```sh
php app/console twitter:count-keywords Secretsales --limit=10
```
You can find more details about the command parameters and options using the help:
```sh
php app/console twitter:count-keywords --help
```
3. If the twitter account is blocked (is not public available) then there will be displayed an error (returned by the Twitter API):
```sh
php app/console twitter:count-keywords md
# Response:
The Twitter request /1.1/statuses/user_timeline.json?count=100&exclude_replies=true&include_rts=false&screen_name=md&trim_user=true failed! 
The response message is: Not authorized.
```
4. If there are any other twitter errors (secret key is wrong, or twitter account doesn't exist) there will be displayed another error:
```sh
php app/console twitter:count-keywords this-account-DoesntExist
# Response:
Error code: 34
Error message: Sorry, that page does not exist.
```
```sh
# in this case the secret key is wrong
php app/console twitter:count-keywords Secretsales --limit=10
# Response:
Error code: 32
Error message: Could not authenticate you.
```
5. To check the unit tests of the `/src/Task/ConsoleBundle/Tests/Services/TwitterServiceTest.php` class, just launch them:
```sh
$ phpunit -c app/
PHPUnit 3.7.28 by Sebastian Bergmann.
Configuration read from /var/www/test/test/app/phpunit.xml.dist
.......
Time: 244 ms, Memory: 6.00Mb
OK (7 tests, 71 assertions)
```
