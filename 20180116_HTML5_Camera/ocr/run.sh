#!/bin/sh
CURRENT=`pwd` 
docker build -t ma1979/html-ocr .
docker run -it --rm -v $CURRENT/camera:/home/camera -v $CURRENT/tmp:/tmp -p 8000:8000 --name html-ocr ma1979/html-ocr bash