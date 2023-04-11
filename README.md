## Pet Shop APIs

Welcome to the documentation of the Pet Shop API! This API allows you to interact with a database of pets and pet owners.
Getting Started

To use this API, you need to first authenticate yourself by obtaining an access token. 
You can do this by sending a POST request to the `/api/v1/user/login` endpoint with your username and password. 
This will return an access token that you can use to make requests to the other endpoints.

Once you have an access token, you can start using the APIs by making requests to the endpoints listed in the documentation.

### Installation Steps
- Clone the repo from GitHub
```
git clone https://github.com/ssiva13/petshop.git
```
- Change Directory `cd petshop`

#### Install with Docker
- Run the bash file after cloning `./install.sh`
  - This command will
      - Pull the docker images needed
      - Install the services defined in `docker-compose.yml`
      - Run the `docker/setup.sh` script which in turn runs
        - `composer install`
        - `npm install`
        - `npm run build`
        - `php artisan migrate --seed`
        - `php artisan jwt:key`
        - `php artisan view:clear`
        - `php artisan config:clear`
        - `php artisan cache:clear`
        - `php artisan key:generate --show`


#### Clean Up Docker
Run the bash file after cloning `./uninstall.sh`

### Run Application
Open app in the browser `http://localhost:8902/`

Use the following seeded credentials to test

- Admin credentials
```
email: admin@buckhill.co.uk
password: adminadmin
```
- User credentials

```
email: marketing@buckhill.co.uk
password: marketingmarketing
```

