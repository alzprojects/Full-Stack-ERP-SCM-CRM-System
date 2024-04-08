CREATE TABLE locations(
    locationID INT PRIMARY KEY,
    address VARCHAR(255),
    name VARCHAR(255),
    rent DECIMAL(10, 2) NOT NULL,
    maintenance DECIMAL(10, 2) NOT NULL,
    utilities DECIMAL(10, 2) NOT NULL
);

CREATE TABLE customers
(
    customerID INT PRIMARY KEY,
    gender VARCHAR(10),
    name VARCHAR(255)
);



CREATE TABLE product (
    productID INT PRIMARY KEY,
    name VARCHAR(255),
    price DECIMAL(10, 2)
);

CREATE TABLE supplier (
    supplierID INT PRIMARY KEY,
    name VARCHAR(255),
    address VARCHAR(255)
);

CREATE TABLE `order` (
    orderID INT PRIMARY KEY,
    orderDate VARCHAR(255) NOT NULL,
    deliveryDate VARCHAR(255),
    orderCost DECIMAL(10, 2) NOT NULL,
    supplierID INT,
    FOREIGN KEY (supplierID) references supplier(supplierID)

);

CREATE TABLE purchase (
    purchaseID INT PRIMARY KEY,
    `date` VARCHAR(255) NOT NULL,
    customerID INT,
    locationID INT,
    satisfactionRating ENUM('Satisfied', 'Ok', 'Bad'),
    FOREIGN KEY (customerID) REFERENCES customers(customerID),
    FOREIGN KEY (locationID) REFERENCES locations(locationID)
);


CREATE TABLE users (
    userID INT PRIMARY KEY,
    start_date VARCHAR(255),
    end_date VARCHAR(255),
    username VARCHAR(100),
    password INT UNIQUE,
    user_type ENUM('employee', 'supplier', 'customer') NOT NULL
);

CREATE TABLE employees (
    employee_id INT PRIMARY KEY NOT NULL,
    userID INT UNIQUE,
    CRMAccess BOOLEAN,
    SCMAccess BOOLEAN,
    ERPAccess BOOLEAN,
    locationID INT,
    FOREIGN KEY (locationID) REFERENCES locations(locationID),
    FOREIGN KEY (userID) REFERENCES users(userID)
);

CREATE TABLE enumSupplier (
    user_id INT UNIQUE PRIMARY KEY,
    supplierID INT NOT NULL,
    FOREIGN KEY (supplierID) REFERENCES supplier(supplierID)
);

CREATE TABLE enumCustomer (
    user_id INT UNIQUE PRIMARY KEY,
    customerID INT NOT NULL,
    FOREIGN KEY (customerID) REFERENCES customers(customerID)
);

CREATE TABLE inventoryDetail (
    inventoryDetailID INT PRIMARY KEY NOT NULL,
    productID INT,
    locationID INT,
    quantity INT,
    FOREIGN KEY (productID) REFERENCES product(productID),
    FOREIGN KEY (locationID) REFERENCES locations(locationID)
);
CREATE TABLE orderDetail (
    orderDetailID INT PRIMARY KEY,
    orderID INT,
    productID INT,
    quantity INT,
    inventoryDetailID INT,
    FOREIGN KEY (orderID) REFERENCES `order`(orderID),
    FOREIGN KEY (productID) REFERENCES product(productID),
    FOREIGN KEY (inventoryDetailID) REFERENCES inventoryDetail(inventoryDetailID)
);

CREATE TABLE purchaseDetail (
    purchaseDetailID INT PRIMARY KEY,
    quantity INT,
    purchaseID INT,
    productID INT,
    inventoryDetailID INT,
    FOREIGN KEY (purchaseID) REFERENCES purchase(purchaseID),
    FOREIGN KEY (productID) REFERENCES product(productID),
    FOREIGN KEY (inventoryDetailID) REFERENCES inventoryDetail(inventoryDetailID)
);