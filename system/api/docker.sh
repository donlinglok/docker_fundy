#!/bin/bash
docker build -t docker/project .
#docker run -t -i docker/project
docker run -d -p 3000:3000 -p 3306:3306 --net=host  docker/project

## Delete all docker containers
# docker rm $(docker ps -a -q)
## Delete all docker images
# docker rmi $(docker images -q)
