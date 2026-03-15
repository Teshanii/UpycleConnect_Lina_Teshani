package main

import (
	"database/sql"
	"fmt"

	_ "github.com/go-sql-driver/mysql"
)

var bd *sql.DB

func initDB() {
	var err error
	bd, err = sql.Open("mysql", "root:root@tcp(127.0.0.1:3306)/upcycle_connect")
	if err != nil {
		fmt.Println("Erreur de connexion à la base de données :", err)
	}
}