CREATE TABLE Vehicle (
    VIN VARCHAR(17) PRIMARY KEY,
    Date DATE,
    Price DECIMAL(10, 2),
    OwnerID INT,
    EmployeeID INT,
    FOREIGN KEY (OwnerID) REFERENCES Owner(ID),
    FOREIGN KEY (EmployeeID) REFERENCES Employee(ID)
);

CREATE TABLE Car (
    FOREIGN KEY (VIN) REFERENCES Vehicle(VIN)
);

CREATE TABLE PickupTruck (
    FOREIGN KEY (VIN) REFERENCES Vehicle(VIN)
);

CREATE TABLE Bus (
    SeatingCapacity INT,
    K12Safe BOOLEAN,
    FOREIGN KEY (VIN) REFERENCES Vehicle(VIN)
);

CREATE TABLE Van (
    WheelchairAccessible BOOLEAN,
    FOREIGN KEY (VIN) REFERENCES Vehicle(VIN)
);
