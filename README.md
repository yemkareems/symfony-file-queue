File conversion demo application

git clone git@github.com:yemkareems/symfony-file-queue.git
cd symfony-file-queue/
git checkout origin/master
composer install
php bin/console app:xml-csv 0
or
php bin/console app:xml-csv 1
php vendor/bin/phpunit tests/Command/Xml2CsVCommandTest.php


If remotefile is 0 the file is read from local
If remotefile is 1 the file is read from ftp

Here i am using CsvWriter to write to CSV. We can use DoctrineWriter to write to DB

