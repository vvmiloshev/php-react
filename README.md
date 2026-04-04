# Photo Gallery & Poll App

## Overview

This project is a full-stack web application built with **PHP (custom backend)** and **React (frontend)**.

It combines two main functionalities:

- Photo Gallery - users can create albums and upload photos  
- Poll System - users can create polls and vote  

The application follows a REST API architecture and uses a relational database with foreign keys.

---

## Features

### Authentication
- User registration
- Login with token-based authentication
- Protected routes in frontend

### Photo Gallery
- Create albums
- Upload photos
- View albums and photos
- Album cover image (auto-selected)

### Poll System
- Create polls with multiple answers
- Activate / close polls
- One active poll at a time
- Vote (only authenticated users)
- View poll results

---

## Run with Docker

### Install Docker
https://www.docker.com/products/docker-desktop

### Start project
docker compose -p php-react up -d --build

### Stop project
docker compose -p php-react down

---

## Run without Docker - not recommended

### Requirements
- PHP >= 8.1
- MySQL >= 8
- Node.js >= 18

### Backend
cd backend

composer install

php -S localhost:8000 -t public

### Frontend
cd frontend

npm install

npm run dev

---

## Notes
- Only one poll can be active at a time
- Only authenticated users can vote
