CREATE TABLE locations (
    locationID INT PRIMARY KEY,
    address VARCHAR(255),
    name VARCHAR(255)
);

CREATE TABLE employees (
    employeeID INT PRIMARY KEY,
    name VARCHAR(255),
    locationID INT,
    FOREIGN KEY (locationID) REFERENCES locations(locationID)
);

CREATE TABLE inventory (
    inventoryID INT PRIMARY KEY,
    quantity INT,
    locationID INT,
    FORIEGN KEY (location) REFERENCES location(locationID)
);

CREATE TABLE product (
    productID INT PRIMARY KEY,
    name VARCHAR(255),
    price DECIMAL(10, 2),
    inventoryID INT,
    FOREIGN KEY (inventoryID) REFERENCES inventory(inventoryID)
);

CREATE TABLE supplier (
    supplierID INT PRIMARY KEY,
    name VARCHAR(255),
);

CREATE TABLE order (
    orderID INT PRIMARY KEY,
    date DATE,
    supplierID INT,
    FOREIGN KEY (supplierID) REFERENCES supplier(supplierID)
);

CREATE TABLE orderDetail (
    orderDetailID INT PRIMARY KEY,
    orderID INT,
    productID INT,
    quantity INT,
    FOREIGN KEY (orderID) REFERENCES order(orderID),
    FOREIGN KEY (productID) REFERENCES product(productID)
);

CREATE TABLE customer (
    customerID INT PRIMARY KEY,
);

CREATE TABLE purchase (
    purchaseID INT PRIMARY KEY,
    date DATE,
    customerID INT,
    FOREIGN KEY (customerID) REFERENCES customer(customerID)
);

CREATE TABLE purchaseDetail (
    purchaseDetailID INT PRIMARY KEY,
    quantity INT,
    purchaseID INT,
    productID INT,
    FOREIGN KEY (purchaseID) REFERENCES purchase(purchaseID),
    FOREIGN KEY (productID) REFERENCES product(productID)
);


