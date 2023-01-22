-- DATABASE MehrMarktDatabase

DROP DATABASE IF EXISTS `MehrMarktDatabase`;



CREATE DATABASE IF NOT EXISTS `MehrMarktDatabase` DEFAULT CHARACTER SET utf8;
USE `MehrMarktDatabase`;

-- User Controll
-- Passwort Muss noch geändert werden
DROP USER IF EXISTS 'MehrMarktUser'@'localhost';

CREATE USER IF NOT EXISTS 'MehrMarktUser'@'localhost' IDENTIFIED BY 'password';
GRANT All Privileges ON MehrMarktDatabase.* TO 'MehrMarktUser'@'localhost';

FLUSH PRIVILEGES;


-- Table `MehrMarktDatabase`.`Lagerplaetze`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Lagerplaetze`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Lagerplaetze`
(
    `Lagerplaetze-ID` INT NOT NULL AUTO_INCREMENT,
    `Name` VARCHAR(4) NOT NULL UNIQUE,
    `ISDELETE`        BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`Lagerplaetze-ID`)
)
    ENGINE = InnoDB;


-- Table `MehrMarktDatabase`.`Lieferanten`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Lieferanten`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Lieferanten`
(
    `Lieferanten-ID` INT         NOT NULL AUTO_INCREMENT,
    `Aktiv`          BOOLEAN     NULL DEFAULT TRUE,
    `Name`           VARCHAR(60) NULL,
    `Strasse`         VARCHAR(30) NULL,
    `Hausnummer`     VARCHAR(5) NULL,
    `Plz`            VARCHAR(15) NULL,
    `Ort`            VARCHAR(30) NULL,
    `Land`           VARCHAR(45) NULL DEFAULT 'Deutschland',
    `Zuverlaessigkeit` double NULL DEFAULT 1,
    `ISDELETE`       BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`Lieferanten-ID`)
)
    ENGINE = InnoDB;

-- Table `MehrMarktDatabase`.`Verkauf/Warenkoerbe`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Verkauf/Warenkoerbe`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Verkauf/Warenkoerbe`
(
    `Warenkorb-ID` INT  NOT NULL AUTO_INCREMENT,
    `Datum`        DATE NULL,
    `ISDELETE`     BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`Warenkorb-ID`)
)
    ENGINE = InnoDB;



-- Table `MehrMarktDatabase`.`Ware`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Ware`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Ware`
(
    `EID`                          VARCHAR(15) NOT NULL,
    `Menge`                        NUMERIC(10,0) NULL,
    `Name`                         VARCHAR(60) NULL,
    `Einkaufspreis`                NUMERIC(10,2) NULL,
    `Verkaufspreis`                NUMERIC(10,2) NULL,
    `Lieferanten_Lieferanten-ID`   INT NULL,
    `Lagerplaetze_Lagerplaetze-ID` INT NOT NULL,
    `ISDELETE`                     BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`EID`),
    INDEX `fk_Ware_Lieferanten1_idx` (`Lieferanten_Lieferanten-ID` ASC) VISIBLE,
    INDEX `fk_Ware_Lagerplaetze1_idx` (`Lagerplaetze_Lagerplaetze-ID` ASC) VISIBLE,
    CONSTRAINT `fk_Ware_Lieferanten1`
        FOREIGN KEY (`Lieferanten_Lieferanten-ID`)
            REFERENCES `MehrMarktDatabase`.`Lieferanten` (`Lieferanten-ID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk_Ware_Lagerplaetze1`
        FOREIGN KEY (`Lagerplaetze_Lagerplaetze-ID`)
            REFERENCES `MehrMarktDatabase`.`Lagerplaetze` (`Lagerplaetze-ID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;

-- Table `MehrMarktDatabase`.`Einkauf`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Einkauf`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Einkauf`
(
    `Einkauf-ID`                 INT  NOT NULL AUTO_INCREMENT,
    `Datum-Ankunft`              DATE NULL,
    `Datum-RealAnkunft`          DATE NULL,
    `Lieferanten_Lieferanten-ID` INT  NOT NULL,
    `ISDELETE`                   BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (`Einkauf-ID`),
    INDEX `fk_Einkauf_Lieferanten1_idx` (`Lieferanten_Lieferanten-ID` ASC) VISIBLE,
    CONSTRAINT `fk_Einkauf_Lieferanten1`
        FOREIGN KEY (`Lieferanten_Lieferanten-ID`)
            REFERENCES `MehrMarktDatabase`.`Lieferanten` (`Lieferanten-ID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;


-- Table `MehrMarktDatabase`.`Ware_has_Verkauf/Warenkoerbe`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Ware_has_Verkauf/Warenkoerbe`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Ware_has_Verkauf/Warenkoerbe`
(
    `Ware_has_Verkauf/Warenkoerb-ID`   INT         NOT NULL AUTO_INCREMENT,
    `Ware_EID`                         VARCHAR(15) NOT NULL,
    `Verkauf/Warenkoerbe_Warenkorb-ID` INT         NOT NULL,
    `Menge`                            NUMERIC(10,0) NULL,
    `Verkaufspreis_State`              NUMERIC(10,2) NULL,
    `ISDELETE`                         BOOLEAN DEFAULT FALSE,
    INDEX `fk_Ware_has_Verkauf/Warenkoerbe_Verkauf/Warenkoerbe1_idx` (`Verkauf/Warenkoerbe_Warenkorb-ID` ASC) VISIBLE,
    INDEX `fk_Ware_has_Verkauf/Warenkoerbe_Ware1_idx` (`Ware_EID` ASC) VISIBLE,
    PRIMARY KEY (`Ware_has_Verkauf/Warenkoerb-ID`),
    CONSTRAINT `fk_Ware_has_Verkauf/Warenkoerbe_Ware1`
        FOREIGN KEY (`Ware_EID`)
            REFERENCES `MehrMarktDatabase`.`Ware` (`EID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk_Ware_has_Verkauf/Warenkoerbe_Verkauf/Warenkoerbe1`
        FOREIGN KEY (`Verkauf/Warenkoerbe_Warenkorb-ID`)
            REFERENCES `MehrMarktDatabase`.`Verkauf/Warenkoerbe` (`Warenkorb-ID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;


-- Table `MehrMarktDatabase`.`Ware_has_Einkauf`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`Ware_has_Einkauf`;

CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`Ware_has_Einkauf`
(
    `Ware_has_Einkauf-ID` INT         NOT NULL AUTO_INCREMENT,
    `Ware_EID`            VARCHAR(15) NOT NULL,
    `Menge`                           NUMERIC(10,0)  NULL,
    `Einkauf_Einkauf-ID`  INT         NOT NULL,
    `Einkaufspreis_State` NUMERIC(10,2) NULL,
    `ISDELETE`            BOOLEAN DEFAULT FALSE,
    INDEX `fk_Ware_has_Einkauf_Einkauf1_idx` (`Einkauf_Einkauf-ID` ASC) VISIBLE,
    INDEX `fk_Ware_has_Einkauf_Ware1_idx` (`Ware_EID` ASC) VISIBLE,
    PRIMARY KEY (`Ware_has_Einkauf-ID`),
    CONSTRAINT `fk_Ware_has_Einkauf_Ware1`
        FOREIGN KEY (`Ware_EID`)
            REFERENCES `MehrMarktDatabase`.`Ware` (`EID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk_Ware_has_Einkauf_Einkauf1`
        FOREIGN KEY (`Einkauf_Einkauf-ID`)
            REFERENCES `MehrMarktDatabase`.`Einkauf` (`Einkauf-ID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
)
    ENGINE = InnoDB;

USE `MehrMarktDatabase`;

-- Placeholder table for view `MehrMarktDatabase`.`view1`
CREATE TABLE IF NOT EXISTS `MehrMarktDatabase`.`view1`
(
    `id` INT
);

-- View `MehrMarktDatabase`.`view1`
DROP TABLE IF EXISTS `MehrMarktDatabase`.`view1`;
DROP VIEW IF EXISTS `MehrMarktDatabase`.`view1`;
USE `MehrMarktDatabase`;
