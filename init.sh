apt-get update
apt-get install -y composer rabbitmq-server git
cd /home/
git clone https://github.com/IliesM/Test.git InstaDm
service rabbitmq-server restart
rabbitmq-plugins enable rabbitmq_management
rabbitmqctl add_user admin admin
rabbitmqctl set_user_tags admin administrator
rabbitmqctl set_permissions -p / admin ".*" ".*" ".*"
apt-get install php7.0-dev
apt-get install php7.0-curl
apt-get install php7.0-mbstring
apt-get install php7.0-curl
apt-get install php7.0-bcmath
apt-get install php7.0-dom
apt-get install php7.0-gd
composer install
