#!/bin/bash
BASEDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )"

cd ${BASEDIR}

echo "Please provide the following configuration values:"
read -r -p "Enter project name: " projectCode
read -r -p "Enter project url without protocol: " projectUrl
echo "Please answer the following prompts (Default value is shown inside [] )"
read -r -p "Enter base folder for project [${BASEDIR}] " projectFolder
read -r -p "Enter Magento edition: (ce/ee) [ce] " magentoEdition
read -r -p "Enter Magento version: [2.2.6] " magentoVersion
read -r -p "Enter username for magento admin: [admin] " magentoUser
read -r -p "Enter email for magento admin: [admin@gammapartners.com] " magentoEmail
read -s -r -p "Enter password for magento admin: [Admin035] " magentoPassword
echo # newline needed due to -s flag
read -r -p "Enter admin URI for magento admin: [admin] " magentoAdminUri
read -s -r -p "Enter password for container user: [Admin035] " linuxPass
echo
linuxUser=${USER}
linuxPass=${linuxPass:-Admin035}
projectFolder=${projectFolder:-${BASEDIR}}
magentoEdition=${magentoEdition:-ce}
magentoVersion=${magentoVersion:-2.2.6}
magentoUser=${magentoUser:-admin}
magentoEmail=${magentoEmail:-admin@gammapartners.com}
magentoPassword=${magentoPassword:-Admin035}
magentoAdminUri=${magentoUser:-admin}

if [[ ${projectCode} == "" ]] || [[ ${projectUrl} == "" ]]; then
    echo "Project Code or Project Url is empty"
    exit 0
fi

read -n 1 -r -p "Update hosts file? [Y/n] " response
response=${response,,}
echo
if [[ ${response} =~ ^(y| ) ]] || [[ -z ${response} ]]; then
    hostsLine=$(echo "127.0.0.1 ${projectUrl}")
    if grep -q "${hostsLine}" /etc/hosts; then
        echo "Hosts file already contains entry for ${projectUrl}. Skipping."
    else
        echo "Preparing hosts file"
        sudo bash -c "echo ${hostsLine} >> /etc/hosts"
    fi
fi

echo "Preparing files"
projectDirectory=${projectFolder}
mkdir -p -v ${projectDirectory}/apache2 ${projectDirectory}/docker/composer

cp apache2/vhosts.conf.dist ${projectDirectory}/apache2/${projectCode}.conf
cp docker/Dockerfile.dist ${projectDirectory}/docker/Dockerfile
cp docker/entrypoint.sh ${projectDirectory}/docker/entrypoint.sh
cp docker/composer/auth.json.dist ${projectDirectory}/docker/composer/auth.json
cp docker-compose.yml.dist ${projectDirectory}/docker-compose.yml

cd ${projectDirectory}
echo "Creating project at $(pwd)"

if [[ "$(uname)" == "Darwin" ]]; then
    sed -i '' -e "s|<PROJECT URL>|${projectUrl}|g" apache2/${projectCode}.conf
    sed -i '' -e "s|<PROJECT NAME>|${projectCode}|g" apache2/${projectCode}.conf
    sed -i '' -e "s|<PROJECT NAME>|${projectCode^^}|g" docker/Dockerfile
    sed -i '' -e "s|<PROJECT NAME>|${projectCode}|g" docker-compose.yml
elif [[ "$(expr substr $(uname -s) 1 5)" == "Linux" ]]; then
    sed -i "s|<PROJECT URL>|${projectUrl}|g" apache2/${projectCode}.conf
    sed -i "s|<PROJECT NAME>|${projectCode}|g" apache2/${projectCode}.conf
    sed -i "s|<PROJECT NAME>|${projectCode^^}|g" docker/Dockerfile
    sed -i "s|<PROJECT NAME>|${projectCode}|g" docker-compose.yml
fi

activeContainers=$(docker ps -q)
if [[ ! -z "${activeContainers// }" ]]; then
	echo "Stopping all current containers"
	docker stop ${activeContainers}
fi

echo "Building containers"
docker-compose build --build-arg USER_NAME=${linuxUser} --build-arg USER_PASS=${linuxPass} --build-arg PROJECT_CODE=${projectCode}

echo "Creating containers"
docker-compose up -d

echo "Installing Magento"
if [[ -f "/var/www/${projectCode}/html/composer.json" ]]; then
    composerCreate="echo Project already installed"
elif [[ ${magentoEdition} == "ce" ]]; then
    composerCreate="composer create-project --repository=https://repo.magento.com/ magento/project-community-edition=${magentoVersion} /var/www/${projectCode}/html/install"
elif [[ ${magentoEdition} == "ee" ]]; then
    composerCreate="composer create-project --repository=https://repo.magento.com/ magento/project-enterprise-edition=${magentoVersion} /var/www/${projectCode}/html/install"
else
    echo "Edition not found"
    exit 0
fi

docker-compose exec web bash -c "${composerCreate}; \
    cp -rT /var/www/${projectCode}/html/install/ /var/www/${projectCode}/html; \
    rm -r /var/www/${projectCode}/html/install; \
    cd /var/www/${projectCode}/html; \
    composer install; \
    chmod +x /var/www/$projectCode/html/bin/magento; \
    magento setup:install --base-url=http://$projectUrl/ \
--db-host=db --db-name=$projectCode \
--db-user=$projectCode --db-password=$projectCode \
--admin-firstname=admin --admin-lastname=admin --admin-email=$magentoEmail \
--admin-user=$magentoUser --admin-password=$magentoPassword --language=en_US \
--currency=USD --timezone=America/Chicago --cleanup-database \
--backend-frontname=$magentoAdminUri --use-rewrites=1; \
    magento setup:config:set --cache-backend=redis \
--cache-backend-redis-server=redis \
--cache-backend-redis-db=0 -n; \
    magento setup:config:set --page-cache=redis \
--page-cache-redis-server=redis \
--page-cache-redis-db=1 \
--page-cache-redis-compress-data=1 -n; \
    magento setup:config:set --session-save=redis \
--session-save-redis-host=redis \
--session-save-redis-db=2 -n; \
 chown -R $linuxUser:$linuxUser /var/www/$projectCode/html;"

echo "Restarting containers"
docker-compose start

echo "All done :)"
echo "Navigate to http://$projectUrl/ to view the site."
