<?php

namespace App\Models\User;

enum PermissionNames: string
{
    case EDIT_PROJECT = 'edit-project';
    case CRUD_DISTRIBUTORS = 'crud-distributors';
    case CRUD_PRODUCTS = 'crud-products';
    case CRUD_INGREDIENTS = 'crud-ingredients';
    case CRUD_DISHES = 'crud-dishes';
    case CRUD_USERS = 'crud-users';
    case CRUD_STORAGE = 'crud-storage';
    case READ_DISTRIBUTORS = 'read-distributors';
    case READ_PRODUCTS = 'read-products';
    case READ_INGREDIENTS = 'read-ingredients';
    case READ_DISHES = 'read-dishes';
    case READ_USERS = 'read-users';
    case READ_STORAGE = 'read-storage';

}