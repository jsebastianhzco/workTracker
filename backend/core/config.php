<?php

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'worktracker';
const DB_USER = 'root';
const DB_PASS = '';

const JWT_SECRET = 'change-this-secret-in-production';
const JWT_ALGO = 'HS256';
const JWT_EXP_SECONDS = 60 * 60 * 8; // 8 horas
