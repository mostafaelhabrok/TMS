# Task Management System
This is a back-end solution for task management system.

## Getting started
To get started, follow these steps:
1. **Clone the repository:**

```
git clone https://github.com/mostafaelhabrok/TMS.git
```

2. **Install dependencies:**

```
cd TMS
```

```
composer install 
```

3. **Add .env file:**

```
composer run-script 'post-root-package-install'
```

4. **Add database parameters in .env file for examble:**

create new database schema and add its params:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=[db_name]
DB_USERNAME=[db_user]
DB_PASSWORD=[db_pass] 
```

5. **Run migrations:**

```
php artisan migrate
```

6. **Generate app key:**

```
php artisan key:generate
```

7. **Start the app:**

```
php artisan serve
```


## Setting Up with Docker
To setup with docker, follow these steps:

1. **Clone the repository:**

```
git clone https://github.com/mostafaelhabrok/TMS.git
```

2. **Build the Docker Image:**

```
cd TMS
```

```
docker-compose build
```

3. **Run the Docker Container:**

```
docker-compose up -d
```


### Now the app is running on [localhost](http://localhost:8000).

## API Endpoint


### Add User
You can add new user by sending a POST request to /api/users endpoint with the user object to add in the request body (to use later for authentication).

#### Example usage:
POST /api/tasks

body: {
            name: 'mostafa',
            email: 'mostafa.elhabrok@gmail.com',
            password: '123456',
            role: 'manager'
      }

This will add new user with the specified values in body.


### Authenticate User
You can get a token for user to use for authentication with other endpoints by sending a POST request to /api/authenticate endpoint with the email and password.

#### Example usage:
POST /api/authenticate

body: {
            email: 'mostafa.elhabrok@gmail.com',
            password: '123456',
      }

This will create new token for the user to use it as **Bearer Token** in header for other endpoints for authorization.


**For all upcoming endpoints use the token you get from **Authenticate User** endpoint as Bearer Token in header.**

### List Tasks
You can retrieve tasks based on specific criteria by sending a GET request to the /api/tasks endpoint with the following query parameters:

title , description , user_id (assigned_user) , due date range (due_date_from , due_date_to) , status.

#### Example usage:
GET /api/tasks?title=task&description=desc&user_id=1&due_date_from=2024-03-01&due_date_to=2024-04-01&status=pending

This will return a filtered list of tasks matching the specified criteria.


### Retrieve Task Details
You can retrieve task details with its dependencies based on id by sending a GET request to the /api/task/{id} endpoint.

#### Example usage:
GET /api/task/1

This will return the task details with its dependencies.


### Add Task
You can add new task by sending a POST request to /api/tasks endpoint with the task object to add in the request body.

#### Example usage:
POST /api/tasks

body: {
            title: 'new task',
            description: 'description',
            user_id: '1',
            due_date: '2024-03-15'
      }

This will add new task with the specified values in body.


### Update Task
You can edit existing task by sending a PUT request to /api/tasks/{id} endpoint with the task object to edit in the request body.

#### Example usage:
PUT /api/tasks/1

body: {
            title: 'new task edit',
            description: 'description',
            user_id: '1',
            due_date: '2024-03-17'
      }

This will edit the task with the specified values in body.


### Add Task Dependency
You can add task dependencies by sending a POST request to /api/tasks/dependency endpoint with the task id and dependency ids.

#### Example usage:
POST /api/tasks/dependency

body: {
            task_id: '3',
            dependency_id[]: '4',
            dependency_id[]: '5',
      }

This will add dependencies for specified task.


### Update Task Status
You can update task status by sending a POST request to /api/tasks/{id}/status endpoint with the status.

#### Example usage:
POST /api/tasks/1/status

body: {
            status: 'pending',
      }

This will update status for specified task.


## Role based authorization

Managers can create/update a task.

Managers can assign tasks to a user.

Users can retrieve only tasks assigned to them.

Users can update only the status of the task assigned to them.



## Postman collection can be found in app root folder.




