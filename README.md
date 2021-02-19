# XDAM
Ximdex Digital Asset Management Module V2

------------

## BACKEND:
#### 	DESCRIPCIÓN:
Es el backend de XDAM, que permite hacer crud con recursos, un recurso es una entidad abstracta a la que se le asocia, un json, y un conjunto de Files o Previews.
Partiendo de esta premisa inicial a este recurso, se le asocian categorías, tags etc...

####	REQUISITOS:
- PHP > 7.4
  Extensiones requeridas:
  sudo apt-get install -y php7.4-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,dom,curl}

- MYSQL 8.0.23
- COMPOSER
- APACHE2 // NGINX
- APACHE SOLR > 6

####	INSTALACIÓN APACHE SOLR:

Para instalar Apache Sol, necesitamos seguir los pasos descritos a continuación:

Instalamos JAVA (se require al menos JAVA > 8)

`sudo apt install openjdk-11-jdk`.

Nos dirigimos a carpeta /opt con:

`cd /opt`

y descargamos e instalamos SOLR:

`wget https://archive.apache.org/dist/lucene/solr/8.5.2/solr-8.5.2.tgz`
`tar xzf solr-8.5.2.tgz solr-8.5.2/bin/install_solr_service.sh --strip-components=2`
`sudo bash ./install_solr_service.sh solr-8.5.2.tgz
`

Para manejar la parada o arranque del servicio solr, tenemos disponible:
`sudo service solr stop`
`sudo service solr start`
`sudo service solr status`

Una vez iniciado el servicio tendremos disponible una interfaz web corriendo en el puerto 8983 ( por defecto ).
Creamos 2 cores necesarios para que funcione XDAM ejecutando en la terminal:
	
`sudo su - solr -c "/opt/solr/bin/solr create -c xdam-course -n data_driven_schema_configs"`

`sudo su - solr -c "/opt/solr/bin/solr create -c xdam-multimedia -n data_driven_schema_configs"`

#### 	INSTALACIÓN BACKEND:

Hacemos un git clone del proyecto con:
`git clone https://github.com/XIMDEX/xdam.git`
y nos dirigimos a la carpeta backend, en su interior ejecutamos:
`composer install`

Copiamos el fichero de ejemplo .env.example a .env

Modificamos el fichero .env para añadir los parámetros de conexión a nuestra base de datos Mysql o MariaDB.

También se debe modificar las variables referentes a SOLR dentro de este fichero, para que apunten a nuestro solr local:

`SOLR_TIMEOUT="60"`

`SOLR_HOST="localhost"`

`SOLR_PORT="8983"`

`SOLR_PATH="/"`

Instalamos los optimizadores de imagen que requiere la librería media-library, con la que se gestionan los ficheros.
###### En Linux:
`sudo apt install jpegoptim optipng pngquant gifsicle`
`npm install -g svgo`
###### En MACOS:
`brew install jpegoptim &&
brew install optipng &&
brew install pngquant &&
brew install svgo &&
brew install gifsicle`

Ejecutamos las migraciones con los seeders con la consola de artisan:
`php artisan migrate --seed`

Una vez hecho esto ya tendremos la base de datos configurada con las tablas y los datos de prueba necesarias.

El siguiente paso es configurar los schemas de Solr, para ello ejecutamos directamente:

`php artisan install:solr`

Este comando se encarga de crear/actualizar los schemas necesarios en los 2 cores del solr que dimos de alta en el apartado de instalación de apache solr.

Con esto hemos concluido la instalación, dentro de la carpeta docs de este repositorio, se encuentra una colección de POSTMAN, que podemos importar en nuestro cliente de postman y probar cada una de las peticiones de la api.

## FRONTEND:
#### 	DESCRIPCIÓN:
Es el frontend de galería de XDAM, escrito en Angular, que consume la API de backend.
#### 	INSTALACIÓN:
Ejecutamos la instalación de las dependencias de npm, con `npm install`.

Podemos leventar un servidor de desarrollo para ver la app en funcionamiento con:
`npm run start`

Las rutas hacia la api de DAM se encuentran bajo el fichero
`/src/app/mappers/endpoints.config.json`
