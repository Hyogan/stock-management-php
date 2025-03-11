en principe, 
les 3 acteurs sont : 

directeur 
. secretaire : 
	- Gerer commandes <-  extends (adjouter[-> include verifier solvalbilite], modifier, supprimer)
	- Etablir bon de livraison(-> include( ajouter commande client)
	- Gerer paiement <- extends etablir facture 
	
. magasinier :
	- Gerer produits <- extends( modifier, supprimer, ajouter ) , 
	- etablir bon de sortie -> include(etablir facture -> include[ajouter commande client]

. directeur: 
	- consulter stock 
	- consulter  bon de commande produit <- extends( approuver, rejeter )
	- gerer utilisateurs ( ajouter, modifier, supprimer,desactiver,  )

  -> un utilisateur effectue une ou plusieurs operations  -- une operation est effectuee par un et un seul utilisateur 
  -> une operation contient un ou plusieurs produits -- un produit est contenu dans 0 ou plusieurs operations
  -> de operation herittent entree et sortie 
  -> une sortie engendre une et une seule livraisons -- une livraison peut etre engendree par 1 ou plusieurs sorties 
  -> une livraison declenche 1 ou plusieurs entree -- une entree est declenchee par une et une seule livraison 
  -> une sortie concerne une et une seule commande -- une commande peut concerner 0 ou plusieurs sorties 
  -> un client peut passer 0 ou plusieurs commandes -- une commande est passee par un et un seul client 


la structure du code : 

├── Controllers/
│   ├── AuthController.php
│   ├── ClientController.php
│   ├── DashboardController.php
│   ├── DeliveryController.php
│   ├── HomeController.php
│   ├── OperationController.php
│   ├── OrderController.php
│   ├── ProductController.php
│   ├── SupplierController.php
│   ├── UserController.php
├── Models/
│   ├── Category.php
│   ├── Client.php
│   ├── Delivery.php
│   ├── Entry.php
│   ├── Exit.php
│   ├── Operation.php
│   ├── Order.php
│   ├── Product.php
│   ├── Supplier.php
│   └── User.php
├── Views/
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── ... (forgot password, reset password, etc.)
│   ├── clients/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ... (show, delete, etc.)
│   ├── dashboard/
│   │   ├── admin.php
│   │   ├── secretary.php
│   │   └── storekeeper.php
│   ├── deliveries/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ...
│   ├── operations/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ...
│   ├── products/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ...
│   ├── suppliers/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ...
│   ├── users/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ...
│   ├── categories/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── ...
│   ├── auth_footer.php
│   ├── auth_header.php
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
│   ├── main.php
│   └── ... (common views, error pages, etc.)
├── Core/
│   ├── App.php
│   ├── Controller.php
│   ├── Model.php
│   └── Database.php
├── Utils/
│   ├── Auth.php
│   ├── Database.php
│   ├── Helpers.php
│   └── Validator.php
├── config/
│   ├── config.php
│   └── database.php
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── ... (images, assets, etc.)
├── routes.php
├── index.php
├── router.php
├── bootstrap.php
├── index.php
├── autoload.php
