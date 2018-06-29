/*
USER_ADMIN
ID      user        rule          villageID
1       Adam        addressbook   1
2       Adam        search        1
3       Bob         addressbook   2
4       Bob         search        1
5       Cyril       addressboook  NULL
6       Cyril       search        2
7       Fred        addressboook  NULL
8       Fred        search        NULL


VILLAGE
ID      NAME
1       Praha
2       Brno
3       Ostrava
*/

CREATE TABLE User_admin (
  ID          INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user        VARCHAR(10) NOT NULL,
  rule        VARCHAR(20) NOT NULL,
  villageID   INT(1),
  CONSTRAINT FK_VillageID FOREIGN KEY (villageID) REFERENCES Village(ID)
);


CREATE TABLE Village (
  ID    INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(10) NOT NULL
);