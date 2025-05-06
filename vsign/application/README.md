# Instructions for VSIGN

1. **Do not forget to create bucket "vsign-bucket" in minio**

2. **To change the permissions of the `laravel.log` file, follow these steps:**:

    To access the container, run the following command:

    ```bash
    docker exec -it <container_name> bash

    cd storage/logs

    chmod 777 laravel.log

    ```
