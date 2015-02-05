#!/bin/bash

# refresh packages
echo "Refreshing packages"
apt-get update > /tmp/vagrant_log 2>&1
apt-get upgrade > /tmp/vagrant_log 2>&1
echo "done"

# install python-software-properties
echo "Installing python-software-properties"
apt-get install -y python-software-properties >> /tmp/vagrant_log 2>&1
echo "done"

# add PPA for PHP 5.4
echo "Adding PPAs for PHP 5.4"
if [ ! -f "/etc/apt/sources.list.d/ondrej-php5-oldstable-precise.list" ];
then
    add-apt-repository -y ppa:ondrej/php5-oldstable >> /tmp/vagrant_log 2>&1
    apt-get update  >> /tmp/vagrant_log 2>&1
fi
echo "done"

echo "Installing vim and set as default editor"
apt-get install -y vim vim-doc vim-scripts mc >> /tmp/vagrant_log 2>&1
update-alternatives --set editor /usr/bin/vim.basic
echo "done"

# install Apache and PHP
echo "Installing Apache and PHP"
apt-get install -y php-apc php5 php5-cli php5-curl php5-gd php5-intl php5-mcrypt php5-mysql php-pear php5-xdebug php5-sqlite php5-dev php5-memcached >> /tmp/vagrant_log 2>&1
echo "done"

echo "Installing Memcached"
apt-get install memcached
service memcached start
echo "done"

# configure Apache and PHP
echo "Configuring Apache and PHP"
if [ -f "/etc/apache2/sites-enabled/000-default" ];
then
    a2dissite default
fi
cp /vagrant/scripts/provision/cube.loc /etc/apache2/sites-available/cube.loc
a2ensite cube.loc
a2enmod rewrite

a2enmod ssl
mkdir /etc/apache2/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=example.com"

cp /vagrant/scripts/provision/php.ini /etc/php5/apache2/php.ini
cp /vagrant/scripts/provision/php.ini /etc/php5/cli/php.ini
cp /vagrant/scripts/provision/xdebug.ini /etc/php5/apache2/conf.d/20-xdebug.ini
sed -i 's/\(APACHE_RUN_USER=\)www-data/\1vagrant/g' /etc/apache2/envvars
chown vagrant:www-data /var/lock/apache2
service apache2 restart
echo "done"

# install MySQL
echo "Installing MySQL server"
export DEBIAN_FRONTEND=noninteractive
apt-get -q -y install mysql-server-5.5 >> /tmp/vagrant_log 2>&1
echo "done"

# install Git
echo "Installing Git"
apt-get install -y git-core >> /tmp/vagrant_log 2>&1
echo "done"

# install composer
echo "Installing composer"
if [ ! -f "/usr/local/bin/composer" ];
then
    php -r "readfile('https://getcomposer.org/installer');" | php
    mv composer.phar /usr/local/bin/composer
fi
echo "done"

echo "Change SSH login dir"
echo "cd /vagrant" >> /home/vagrant/.bashrc
echo "done"

echo "Creating MySQL DB"
mysql -uroot -e "DROP DATABASE IF EXISTS magento;"
mysql -uroot -e "CREATE DATABASE magento;"
mysql -uroot -D magento < /vagrant/scripts/provision/magento_sample_data_for_1.9.0.0.sql
echo "done"

echo "Installing Magento"
php -f /vagrant/install.php -- --license_agreement_accepted 'yes' --locale 'en_US' --timezone 'America/Los_Angeles' --default_currency 'USD' --db_host '127.0.0.1' --db_name 'magento' --db_user 'root' --db_pass '' --use_secure 'no' --use_secure_admin 'no' --use_rewrites 'yes' --admin_lastname 'Admin' --admin_firstname 'Admin' --admin_email 'admin@example.com' --admin_no_form_key 'yes' --url 'http://cube.loc/' --secure_base_url 'http://cube.loc/' --admin_username 'admin' --admin_password '123123q' 2>&1
echo "done"
