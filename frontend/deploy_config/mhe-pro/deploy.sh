PROJECT_ROOT=$(pwd)
CONFIG_FOLDER=deploy_config
CONFIG_PATH=mhe-pro

echo $PROJECT_ROOT

cd $PROJECT_ROOT
rm .env

cp $PROJECT_ROOT/$CONFIG_FOLDER/$CONFIG_PATH/.env $PROJECT_ROOT/.env

npm run build

NOW="$(date +'%d-%m-%Y_%H-%M-%S')"
OUTPUT=build-$NOW-$CONFIG_PATH.zip

zip -r $OUTPUT build/
mv build-*.zip $PROJECT_ROOT/$CONFIG_FOLDER/$CONFIG_PATH/builds

rm $PROJECT_ROOT/.env