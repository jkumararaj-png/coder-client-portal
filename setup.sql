-- ALL USER PASSWORDS ARE "password"
CREATE DATABASE coder_client_portal;

USE coder_client_portal;

-- Roles include Admin, Coder, and Client.
CREATE TABLE IF NOT EXISTS
    ROLES (
        role_id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(20) UNIQUE NOT NULL
    );

INSERT INTO
    ROLES (role_name)
VALUES
    ("admin"),
    ("coder"),
    ("client");

SELECT
    *
FROM
    ROLES;

-- Users can either be Admin, Coder, or Client, referencing the role_id column from the roles table
CREATE TABLE IF NOT EXISTS
    users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role_id INT NOT NULL,
        CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES ROLES (role_id)
    );

-- Create admin user
INSERT INTO
    users (name, email, password, role_id)
VALUES
    (
        'Admin User',
        'admin@test.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        (
            SELECT
                role_id
            FROM
                ROLES
            WHERE
                role_name = 'admin'
        )
    );

-- Create coder user
INSERT INTO
    users (name, email, password, role_id)
VALUES
    (
        'Coder Demo',
        'coder@test.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        (
            SELECT
                role_id
            FROM
                ROLES
            WHERE
                role_name = 'coder'
        )
    );

-- Create client user
INSERT INTO
    users (name, email, password, role_id)
VALUES
    (
        'Client Demo',
        'client@test.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        (
            SELECT
                role_id
            FROM
                ROLES
            WHERE
                role_name = 'client'
        )
    );

SELECT
    *
FROM
    users;

CREATE TABLE IF NOT EXISTS
    projects (
        project_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(50) NOT NULL,
        description VARCHAR(1000) NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        github_link VARCHAR(500) DEFAULT NULL,
        status INT NOT NULL,
        coder_id INT NOT NULL,
        client_id INT,
        CONSTRAINT fk_projects_coder FOREIGN KEY (coder_id) REFERENCES users (user_id),
        CONSTRAINT fk_projects_client FOREIGN KEY (client_id) REFERENCES users (user_id)
    );

-- Test project
INSERT INTO
    projects (
        title,
        description,
        image,
        status,
        coder_id,
        client_id
    )
VALUES
    (
        'E-commerce Platform',
        'Build a full-stack e-commerce platform with payment integration',
        'project_1770644319_6989e35f98142.png',
        'https://github.com/jkumararaj-png/coder-client-portal',
        1,
        (
            SELECT
                user_id
            FROM
                users
            WHERE
                email = 'coder@test.com'
        ),
        (
            SELECT
                user_id
            FROM
                users
            WHERE
                email = 'client@test.com'
        )
    );

SELECT
    *
FROM
    projects;

CREATE TABLE IF NOT EXISTS
    feedback (
        feedback_id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        client_id INT NOT NULL,
        message VARCHAR(1000) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_feedback_project FOREIGN KEY (project_id) REFERENCES projects (project_id),
        CONSTRAINT fk_feedback_client FOREIGN KEY (client_id) REFERENCES users (user_id)
    );