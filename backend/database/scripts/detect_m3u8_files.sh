#!/bin/bash

directory="./storage/app/public"

for resource in "$directory"/*
do
	for file in "$resource"/*
	do
		if [[ $file == *.m3u8 ]]
		then
			echo $file
		fi
	done
done

exit 0
