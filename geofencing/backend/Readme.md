Create a database for storing the points which are clicked 
My database name is called businessdb
create table called points with this sql querry

CREATE TABLE points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL,
    user_ip VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


im using php and xammp for handling the requests in the backend
