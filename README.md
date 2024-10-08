# What is XDAM?
The Digital Asset Management service of the Ximdex Platform (XDAM) provides a single point of interaction with all types of digital objects (images, videos, audio, e- books, documents, fonts, links, websites, etc.), categorizing them for quick and efficient search based on customizable facets.

# Installation of XDAM Backend

## System Requirements

| Technology    | Version |
|---------------|---------|
| PHP           | 8.2     |
| Composer      | Latest  |
| Node.js       | 14      |
| NGINX         | Latest  |
| MySQL         | 8       |
| Apache Solr   | 8       |

On Linux, to install PHP with its corresponding extensions, use:

```bash
sudo apt-get install -y php8.2-bcmath php8.2-bz2 php8.2-intl php8.2-gd php8.2-mbstring php8.2-mysql php8.2-zip php8.2-common php8.2-dom php8.2-curl php8.2-imagick php8.2-fpm
```

## 1. Download the Repository

First, clone the repository from GitHub using the following command:

```bash
git clone https://github.com/XIMDEX/xdam.git
```


## 2. Environment Configuration
Create and configure the .env file. To do this, first go to the backend folder. You can use the .env.example file as a base:
```bash
cd backend
cp .env.example .env
nano .env
```

## 3. Install Dependencies

To install the necessary dependencies, run the following command:

```bash
composer install
```

## 4. Database Migration

After setting up your environment variables in the .env file, you need to run database migrations. This step creates the necessary tables in your database according to the defined schema.

Run the following command in your terminal:

```bash
php artisan migrate
```

## 5. Solr Configuration

For Solr, execute the following commands:

```bash
sudo service solr start
sudo su - solr -c "/opt/solr/bin/solr create -c activity -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c assessment -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c course -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c multimedia -n data_driven_schema_configs"
sudo su - solr -c "/opt/solr/bin/solr create -c book -n data_driven_schema_configs"
```

## 6. Install Additional Dependencies

You'll need to install the following additional dependencies:

```bash
composer install
sudo apt install jpegoptim optipng pngquant gifsicle ffmpeg
sudo npm install -g svgo
```


## 7. Final Configuration Steps

Finally, execute the following commands:

```bash
sudo php artisan solrCores:maintenance --action=ALL
sudo php artisan optimize:clear
sudo php artisan passport:install
php artisan db:seed
```
