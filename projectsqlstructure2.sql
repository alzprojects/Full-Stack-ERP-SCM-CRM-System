-- Locations
CREATE TABLE locations (
    locationID INT PRIMARY KEY,
    address VARCHAR(255),
    name VARCHAR(255)
);

-- Employees
CREATE TABLE employees (
    employeeID INT PRIMARY KEY,
    name VARCHAR(255)
);

-- Inventory
CREATE TABLE inventory (
    inventoryID INT PRIMARY KEY,
    quantity INT
);

-- Product
CREATE TABLE product (
    productID INT PRIMARY KEY,
    name VARCHAR(255),
    price DECIMAL(10, 2)
);

-- Supplier
CREATE TABLE supplier (
    supplierID INT PRIMARY KEY,
    name VARCHAR(255)
);

-- Order
CREATE TABLE `order` (
    orderID INT PRIMARY KEY,
    date DATE
);

-- Customer
CREATE TABLE customer (
    customerID INT PRIMARY KEY
    Name VARCHAR(255)
    PhoneNum INT
);

-- Purchase
CREATE TABLE purchase (
    purchaseID INT PRIMARY KEY,
    date DATE
);

-- EmployeeLocations
CREATE TABLE EmployeeLocations (
    employeeID INT,
    locationID INT,
    PRIMARY KEY (employeeID, locationID),
    FOREIGN KEY (employeeID) REFERENCES employees(employeeID),
    FOREIGN KEY (locationID) REFERENCES locations(locationID)
);

-- InventoryLocations
CREATE TABLE InventoryLocations (
    inventoryID INT,
    locationID INT,
    PRIMARY KEY (inventoryID, locationID),
    FOREIGN KEY (inventoryID) REFERENCES inventory(inventoryID),
    FOREIGN KEY (locationID) REFERENCES locations(locationID)
);

-- ProductInventory
CREATE TABLE ProductInventory (
    productID INT,
    inventoryID INT,
    PRIMARY KEY (productID, inventoryID),
    FOREIGN KEY (productID) REFERENCES product(productID),
    FOREIGN KEY (inventoryID) REFERENCES inventory(inventoryID)
);

-- OrderSupplier
CREATE TABLE OrderSupplier (
    orderID INT,
    supplierID INT,
    PRIMARY KEY (orderID, supplierID),
    FOREIGN KEY (orderID) REFERENCES `order`(orderID),
    FOREIGN KEY (supplierID) REFERENCES supplier(supplierID)
);

-- PurchaseCustomer
CREATE TABLE PurchaseCustomer (
    purchaseID INT,
    customerID INT,
    PRIMARY KEY (purchaseID, customerID),
    FOREIGN KEY (purchaseID) REFERENCES purchase(purchaseID),
    FOREIGN KEY (customerID) REFERENCES customer(customerID)
);

-- OrderDetail
CREATE TABLE orderDetail (
    orderDetailID INT PRIMARY KEY,
    orderID INT,
    productID INT,
    quantity INT,
    FOREIGN KEY (orderID) REFERENCES `order`(orderID),
    FOREIGN KEY (productID) REFERENCES product(productID)
);

-- PurchaseDetail
CREATE TABLE purchaseDetail (
    purchaseDetailID INT PRIMARY KEY,
    quantity INT,
    purchaseID INT,
    productID INT,
    FOREIGN KEY (purchaseID) REFERENCES purchase(purchaseID),
    FOREIGN KEY (productID) REFERENCES product(productID)
);
