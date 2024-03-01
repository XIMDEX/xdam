XDAM
====

Ximdex Digital Asset Management Module V2

------------

## BACKUPS:

Existe un paquete de gestión de backups instalado en este repositorio https://github.com/spatie/laravel-backup , que hace una exportación de la base de datos asociada, así como una copia de todo el repositorio generando un zip.

Para ejecutar una copia de seguridad se hace con el comando:
```shell
php artisan backup:run
```

Esto generará un fichero de backup en la ruta /storage/app/{APP_NAME}/

La documentación de este sistema de copias de seguridad está presente aquí:

https://spatie.be/docs/laravel-backup/v7/introduction

Dentro de config/backup.php podemos cambiar la configuración del sistema.

Para añadir más rutas donde se copiará esta copia de seguridad o bien para cambiar la ruta de generación debemos:

1) Dar de alta un nuevo filesystem en config/filesystem.php
2) Modificar el fichero config/backup.php y añadir el nuevo disco en el array disks dentro de 'destination' o bien sustituir el contenido y dejar solo el nuevo disco.
3) php artisan config:clear y php artisan backup:run, con esta configuración el fichero de backup se guardaría tanto en el disco local como en el nuevo configurado.


## FRONTEND:
El FRONTEND de XDAM está ahora en un repositorio separado. Mirar su documentación.

## BACKEND:

### 	DESCRIPCIÓN:

Es el backend de XDAM, que permite hacer crud con recursos, un recurso es una entidad abstracta a la que se le asocia, un json, y un conjunto de Files o Previews.
Partiendo de esta premisa inicial a este recurso, se le asocian categorías, tags etc...

### A) INSTALACION CON DOCKER Y MAKE

De esta forma no necesitarías instalar ni configurar los diferentes servicios en tu propio equipo,
tales como php-fpm, npm, servidor httpd o bases de datos

1) La primera vez para construir las imagenes de docker necesarias, y hacer la instalación de Laravel
   lanzamos la siguiene receta de Make:
   ```shell
   $ make fresh-start
   ```
   Éste proceso solo debería ser necesario la primera vez, y reinicializa todos los datos y volúmenes
   (así que atención si no quieres **perder tus datos locales**).

2) Si ya tenemos todo construído con `make fresh-start`, en sucesivas ocasiones que queramos levantar el servicio,
   usaremos:
   ```shell
   $ make up
   ```
3) Para detener el servicio:
   ```shell
   $ make stop

### B) INSTALACION EN LOCAL

####	REQUISITOS:

- PHP > 7.4
  Extensiones requeridas:
  sudo apt-get install -y php7.4-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,dom,curl}

- MYSQL 8.0.23
- COMPOSER
- APACHE2 // NGINX
- APACHE SOLR > 6

####	INSTALACIÓN APACHE SOLR:

Para instalar Apache Solr, necesitamos seguir los pasos descritos a continuación:

Instalamos JAVA (se require al menos JAVA > 8)
```shell
sudo apt install openjdk-11-jdk
```

Nos dirigimos a carpeta /opt con:
```shell
cd /opt
```

y descargamos e instalamos SOLR:
```shell
wget https://archive.apache.org/dist/lucene/solr/8.5.2/solr-8.5.2.tgz
tar xzf solr-8.5.2.tgz solr-8.5.2/bin/install_solr_service.sh --strip-components=2
sudo bash ./install_solr_service.sh solr-8.5.2.tgz
```

Para manejar la parada o arranque del servicio solr, tenemos disponible:
```shell
sudo service solr stop
sudo service solr start
sudo service solr status
```

Una vez iniciado el servicio tendremos disponible una interfaz web corriendo en el puerto 8983 (por defecto).
Creamos los cores necesarios para que funcione XDAM ejecutando en la terminal, debemos crear un core por cada uno de los cores definidos en config/solarium.php:
```shell	
sudo su - solr -c "/opt/solr/bin/solr create -c activity -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c assessment -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c course -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c multimedia -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c book -n data_driven_schema_configs"
```

#### 	INSTALACIÓN BACKEND:

y nos dirigimos a la carpeta backend, en su interior ejecutamos:
```shell
composer install
npm install
```

Copiamos el fichero de ejemplo .env.example a .env

Modificamos el fichero .env para añadir los parámetros de conexión a nuestra base de datos Mysql o MariaDB.

Configuramos sistema de tokens con:
```shell
php artisan passport:install
```

Instalamos los optimizadores de imagen que requiere la librería media-library, con la que se gestionan los ficheros.

###### En Linux:

```shell
sudo apt install jpegoptim optipng pngquant gifsicle ffmpeg
npm install -g svgo
```

También es necesario instalar el modulo ImageMagick para la versión de php que tengamos y activarlo en el php.ini añadiendo o descomentando `extension=imagick.so`

Ejemplo si es php 7.4.3
```shell
sudo apt install php7.4-imagick
```

###### En MACOS:

```shell
brew install jpegoptim &&
brew install optipng &&
brew install pngquant &&
brew install svgo &&
brew install gifsicle
```

Ejecutamos las migraciones con los seeders con la consola de artisan:
```
php artisan migrate --seed
```

Una vez hecho esto ya tendremos la base de datos configurada con las tablas y los datos de prueba necesarias.

El siguiente paso es configurar los schemas de Solr.


## CONFIGURACIÓN APACHE SOLR - COLECCIONES:

El sistema está preparado para soportar la indexación de recursos, en varios cores dentro de un mismo apache solr, y también en distintos.

Un recurso dentro de XDAM, está asociado a una colección, (tabla collections) y una colección tiene un campo solr_conection donde definimos a que nombre de conexión se indexará el recurso.

XDAM tiene definidas estas conexiones por defecto, en cada una de ellas se indica a que solr se van a indexar (fichero config/solarium.php)

* Assessment 
* Activities
* Course
* Multimedia
* Book

El proyecto inicial también crea una serie de colecciones que tienen en su campo solr_connection los nombres de cada una de ellas.

Por tanto en una instalación estándar, tendremos una serie de colecciones que están asociadas a una conexión solr.

Durante el proceso de instalación debermos ejecutar el siguiente comando:
```shell
php artisan solr:install
```

Este comando lo que hace es recorrer cada una de las conexiones de solr y hacer una instalación de los schemas para cada colección. 

Una vez ejecutado el sistema ya estará preparado para añadir recursos.

## FAQS
### ¿COMO FUNCIONAN LOS RECURSOS?

Un recurso, como hemos comentado es una entidad abstracta que tiene un json asociado, así como un archivo o varios, y una preview o varias.

Cuando se da alta un recurso, debe añadirse un json, y opcionalmente un fichero o una preview. 
También debermos indicar una id de coleccion a la que se asociara el recurso.

Por medio del sistema de connections del paso anterior, cuando añadamos un recurso, al json que lleva asociado se le pasará un validador dependiendo de la colección adonde queramos guardarlo.

Respecto a las previews y los ficheros, siempre se generará una `damUrl` de previsualización.

Una dam_url es un link único que sirve tanto para descargar ese preview o fichero, como para renderizarlo, para ello existen métodos específicos en la api /render y Y/download.

Hay ejemplos interactuando con recursos dentro de la carpeta docs en la colección de postman subida en el repositorio.


### ¿COMO FUNCIONA EL SISTEMA DE CONEXIONES A APACHE SOLR?

El sistema de conexiones a solr, permite indexar un recurso asociado a una colección en una instancia de apache solr concreta, además dependiendo de la conexión se tiene disponible un schema determinado así como un validador json de los datos de entrada provinientes del usuario. 

Para que el sistema de conexiones a solr funcione, hay que crear una serie de ficheros asociados a cada entrada del fichero config/solarium.php

Por tanto una conexión a apache solr necesita de la siguiente configuración:

- Una entrada en el json presente en config/solarium.php: 
	- Aquí se define el mapeo inicial de conexión con su solr core correspondiente:
	 
	-
		```php
		['nombreConexion' => [ # Nombre de la conexión puede ser cualquier nombre
		    'endpoint' => [ 
		        'scheme' => 'http', # o https
		        'host' => env('SOLR_HOST', 'localhost'), # define ip/dns de solr  
		        'port' => env('SOLR_PORT', '8983'), # define puerto de solr,
		        'path' => env('SOLR_PATH', '/'), # define ruta root de solr,
		        'core' => 'multimedia', # define core objetivo (debe haber sido dado de alta en el proceso de instalación de solr,
		    ],
		    'resource' => 'nombreConexionSolrResource', # Debe ser una clase existente dentro de la ruta /backend/app/Http/Resources/Solr
		]];
		```
	- Por tanto si queremos añadir una nueva conexión, deberemos hacerlo primeramente añadiendo la nueva entrada en este fichero

- Debe existir un fichero dentro de /backend/storage/solr_schemas compuesto de nombre de conexión + ".schema.json", este fichero es una definición de schema de solr en json, es el que se ejecuta cuando se hace `php artisan solr:install`.
- Debe existir un fichero dentro de /backend/storage/solr_validators compuesto de nombre de conexión + "validator.json", este fichero es un json schema, que se encargará de validar el json proviniente del usuario.
- Una clase dentro de /backend/app/Http/Resources/Solr, compuesta de nombreconexión + SolrResource, esta clase se encarga de mapear la entidad DamResource al json que finalmente se indexará en solr.

Con estos 4 items, el sistema generará clientes de conexion para cada solr_connection, donde cada uno de ellos ya sabrá donde debe indexar el recurso, como validar el json recibido por el usuario, así como que mapeo debe realizar entre el modelo dam_resources y el json destino de indexación.


### ¿CÓMO DAR DE ALTA UNA NUEVA CONEXIÓN A APACHE SOLR?

Si se quiere añadir una conexión a este pool inicial, lo que hay que hacer es generar lo siguiente:
- Una nueva entrada en config/solarium.php
- Un fichero en /backend/storage/solr_validators siguiendo nomenclatura de nombres y adaptar contenido.
- Un fichero en /backend/storage/solr_schemas siguiendo nomenclatura de nombres y adaptar contenido.
- Un fichero en /backend/app/Http/Resources/Solr siguiendo nomenclatura de nombres y adaptar contenido.
Una vez hecho esto debería ejecutarse el nuevo schema con:
	```shell
	php artisan solr:install
	```

Y por último debería añadirse una colección a la tabla collections que contenga en su campo solr_connection el nombre de la nueva conexión, o bien modificar alguna de las existentes cambiando el solr_connectionl.


## ¿COMO SE VUELVEN A INDEXAR LOS RECURSOS?

Existe un comando específico php artisan solr:reindex, que recorrerá todos los recursos presentes en la base de datos y los indexará en el apache solr correspondiente.

Hay también un comando especial, php artisan solr:clean que borra todos los documentos indexados en cada una de las instalaciones de solr asociadas.

### New
Se ha añadido un nuevo comando `php artisan solr:update --query=""` que permite actualizar recursos presentes en la base de datos a partir de una query SQL.
Se ha añadido un nuevo comando de gestión de Cores: `php artisan solrCoresMaintenace`, este comando accepta los parámetros:
* `--action=` -> `DELETE`, `CREATE`, `REINDEX` y `ALL`
* `--core=` -> cores separados por comas de los que se quiere realizar la acción
* `--exclude=` -> contrario del parámetro core, excluirá los cores indicados
* `--y` -> no pedirá confirmación en las acciones

**_Ejemplo_**: 
	
		para crear los cores en su versión número 10 ejecutaríamos `sudo php artisan solrCores:maintenance --action=CREATE --coreVersion=10$`. En el .env del backend hay que añadir el parámetro con la versión a la que apuntamos: 
SOLR_CORES_VERSION="10".

## ¿COMO MOVER RECURSOS DE UN SOLR/CORE A OTRO?

Bastaría con modificar en la base de datos, dentro de la tabla collections, la solr_connection por la nueva conexión.

Una vez hecho esto habría que ejecutar un php artisan solr:reindex, que se encargaría de reindexar todos los recursos a las conexiones actuales.

# UPDATE - 01/03/2024 

## Instalation from zero

1. Cambiar a la rama `develop` (modo desarrollo)
2. Ejecutar `composer install`, a tener en cuenta:
    * El `composer.lock` está erróneo y no actualizado con la nueva versión, he tenido que borrarlo para que tome las nuevas versiones.
    * Había un error en el código en `backend/app/Enums/Roles.php` en el constructor donde se buscaban los roles en la tabla Roles de la bbdd cuando aún no se ha hecho la migración por lo que fallaba en el `post-autoload-dump`, he modificado el constructor para que si da fallo la query por no existir tablas, cargue un array vacio de roles
3. Crear tablas y poblar la bbdd ejecutando `php artisan migrate --seed`
4. Generar una passport key con `php artisan passport:install`
