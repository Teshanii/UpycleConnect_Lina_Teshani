


set -e


if [ ! -f .env ]; then
  echo "ERREUR : le fichier .env est manquant."
  echo "Cree-le a la racine du projet (voir le README)."
  exit 1
fi


echo "Demarrage des conteneurs Docker..."
docker compose up -d --build

echo "Attente de la base de donnees..."
until docker exec upcycle_db mysqladmin ping -h localhost --silent 2>/dev/null; do
  sleep 2
done
echo "Base de donnees prete."


echo "Chargement de la base remplie..."
docker exec -i upcycle_db mysql -u root -proot upcycle_connect < base_remplie.sql
echo "Donnees chargees."

#Fin
echo ""
echo "Installation terminee !"
echo "Site accessible sur : http://localhost"
echo "Comptes de demo : mot de passe Demo_2026 (ex : marie.dubois@gmail.com)"
