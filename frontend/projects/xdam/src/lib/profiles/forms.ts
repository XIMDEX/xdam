import { File } from './../models/forms/questions/File';
import { Text } from '../models/forms/questions/Text';

export const itemInfo = [
           new Text({
               realName: 'nameField',
               key: 'title',
               label: 'Name'
           }),
           new Text({
               realName: 'descriptionField',
               key: 'description',
               label: 'Description'
           }),
           new Text({
               realName: 'authorField',
               key: 'author',
               label: 'Author'
           })
       ];

export const fileForm = [
    new File({
        realName: 'fileField',
        key: 'resource',
        label: 'New File'
    })
];
