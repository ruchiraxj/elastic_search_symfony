# Elastic Search On Symfony

## Set up server

1. Clone repo
2. Go inside the folder
3. Run composer install
4. Create a new database "es_symfony" and change DB configs in /.env file
5. Run database migration using "php bin/console doctrine:migrations:migrate"

## Execute APIs

##### Postman collection of the APIs are available in ./elastic_search_symfony.postman_collection.json

1. Import postman collection
2. Execute "User - Register"
3. Execute "User - Login" and obtain JW Token and update Authentication header in other API calls with this
4. Execute other APIs using necessary parameters

### NOTES: 
##### API URLS might need to change according to your localhost environment
##### Elastic Search logics are stored in src/Services/ElasticQueries.php
