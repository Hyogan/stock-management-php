<?php
/**
 * Modèle Commande
 */
class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Récupérer toutes les commandes
     */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             ORDER BY c.date DESC"
        );
    }
    
    /**
     * Récupérer une commande par son numéro
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT c.*, cl.nom, cl.prenom, cl.telephone, cl.ville, cl.quartier
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.numero_commande = ?",
            [$id]
        );
    }
    
    /**
     * Créer une nouvelle commande
     */
    public function create($data, $products) {
        // Commencer une transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Vérifier la solvabilité du client si nécessaire
            if (isset($data['check_solvency']) && $data['check_solvency']) {
                $clientModel = new Client();
                if (!$clientModel->checkSolvency($data['id_client'])) {
                    throw new Exception("Le client n'est pas solvable pour cette commande.");
                }
            }
            
            // Calculer le montant total et le nombre de produits
            $totalAmount = 0;
            foreach ($products as $product) {
                $totalAmount += $product['quantite'] * $product['prix_unitaire'];
            }
            
            // Créer la commande
            $orderData = [
                'numero_commande' => $data['numero_commande'] ?? generateReference('CMD'),
                'date' => $data['date'] ?? date('Y-m-d H:i:s'),
                'montant' => $totalAmount,
                'nbr_produit' => count($products),
                'id_client' => $data['id_client'],
                'statut' => $data['statut'] ?? ORDER_PENDING,
                'statut_paiement' => $data['statut_paiement'] ?? PAYMENT_PENDING
            ];
            
            $orderId = $this->db->insert('commande', $orderData);
            
            // Ajouter les produits à la commande
            foreach ($products as $product) {
                $this->db->insert('commande_produit', [
                    'numero_commande' => $orderId,
                    'id_produit' => $product['id_produit'],
                    'quantite' => $product['quantite'],
                    'prix_unitaire' => $product['prix_unitaire']
                ]);
            }
            
            // Valider la transaction
            $this->db->getConnection()->commit();
            
            return $orderId;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Mettre à jour une commande
     */
    public function update($id, $data, $products = null) {
        // Commencer une transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            $orderData = [
                'date' => $data['date'] ?? null,
                'id_client' => $data['id_client'] ?? null,
                'statut' => $data['statut'] ?? null,
                'statut_paiement' => $data['statut_paiement'] ?? null
            ];
            
            // Filtrer les valeurs null
            $orderData = array_filter($orderData, function($value) {
                return $value !== null;
            });
            
            // Si des produits sont fournis, mettre à jour les produits et recalculer le montant
            if ($products !== null) {
                // Calculer le montant total et le nombre de produits
                $totalAmount = 0;
                foreach ($products as $product) {
                    $totalAmount += $product['quantite'] * $product['prix_unitaire'];
                }
                
                $orderData['montant'] = $totalAmount;
                $orderData['nbr_produit'] = count($products);
                
                // Supprimer les produits actuels
                $this->db->delete('commande_produit', 'numero_commande = ?', [$id]);
                
                // Ajouter les nouveaux produits
                foreach ($products as $product) {
                    $this->db->insert('commande_produit', [
                        'numero_commande' => $id,
                        'id_produit' => $product['id_produit'],
                        'quantite' => $product['quantite'],
                        'prix_unitaire' => $product['prix_unitaire']
                    ]);
                }
            }
            
            // Mettre à jour la commande
            if (!empty($orderData)) {
                $this->db->update('commande', $orderData, 'numero_commande = ?', [$id]);
            }
            
            // Valider la transaction
            $this->db->getConnection()->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Supprimer une commande
     */
    public function delete($id) {
        // Commencer une transaction
        $this->db->getConnection()->beginTransaction();
        
        try {
            // Vérifier si la commande a des sorties associées
            $exits = $this->db->fetchAll(
                "SELECT id_sortie FROM commande_sortie WHERE numero_commande = ?",
                [$id]
            );
            
            if (!empty($exits)) {
                throw new Exception("Impossible de supprimer la commande car elle a des sorties associées.");
            }
            
            // Supprimer les produits de la commande
            $this->db->delete('commande_produit', 'numero_commande = ?', [$id]);
            
            // Supprimer la commande
            $this->db->delete('commande', 'numero_commande = ?', [$id]);
            
            // Valider la transaction
            $this->db->getConnection()->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Récupérer les produits d'une commande
     */
    public function getProducts($id) {
        return $this->db->fetchAll(
            "SELECT cp.*, p.designation
             FROM commande_produit cp
             JOIN produit p ON cp.id_produit = p.id_produit
             WHERE cp.numero_commande = ?",
            [$id]
        );
    }
    
    /**
     * Approuver une commande
     */
    public function approve($id) {
        return $this->db->update(
            'commande',
            ['statut' => ORDER_APPROVED],
            'numero_commande = ?',
            [$id]
        );
    }
    
    /**
     * Rejeter une commande
     */
    public function reject($id) {
        return $this->db->update(
            'commande',
            ['statut' => ORDER_REJECTED],
            'numero_commande = ?',
            [$id]
        );
    }
    
    /**
     * Mettre à jour le statut de paiement d'une commande
     */
    public function updatePaymentStatus($id, $status) {
        return $this->db->update(
            'commande',
            ['statut_paiement' => $status],
            'numero_commande = ?',
            [$id]
        );
    }
    
    /**
     * Récupérer les commandes par client
     */
    public function getByClient($clientId) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.id_client = ?
             ORDER BY c.date DESC",
            [$clientId]
        );
    }
    
    /**
     * Récupérer les commandes par statut
     */
    public function getByStatus($status) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.statut = ?
             ORDER BY c.date DESC",
            [$status]
        );
    }
    
    /**
     * Récupérer les commandes par statut de paiement
     */
    public function getByPaymentStatus($status) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.statut_paiement = ?
             ORDER BY c.date DESC",
            [$status]
        );
    }
    
    /**
     * Récupérer les commandes par période
     */
    public function getByPeriod($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT c.*, cl.nom, cl.prenom
             FROM commande c
             JOIN client cl ON c.id_client = cl.id_client
             WHERE c.date BETWEEN ? AND ?
             ORDER BY c.date DESC",
            [$startDate, $endDate]
        );
    }
    
    /**
     * Récupérer les statistiques des commandes par période
     */
    public function getStatsByPeriod($period = 'month') {
        $sql = "";
        
        switch ($period) {
            case 'day':
                $sql = "SELECT DATE(date) as period, COUNT(*) as count, SUM(montant) as total
                        FROM commande
                        GROUP BY DATE(date)
                        ORDER BY DATE(date) DESC
                        LIMIT 30";
                break;
            case 'week':
                $sql = "SELECT YEAR(date) as year, WEEK(date) as week, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM commande
                        GROUP BY YEAR(date), WEEK(date)
                        ORDER BY YEAR(date) DESC, WEEK(date) DESC
                        LIMIT 12";
                break;
            case 'month':
            default:
                $sql = "SELECT YEAR(date) as year, MONTH(date) as month, 
                               COUNT(*) as count, SUM(montant) as total
                        FROM commande
                        GROUP BY YEAR(date), MONTH(date)
                        ORDER BY YEAR(date) DESC, MONTH(date) DESC
                        LIMIT 12";
                break;
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Récupérer les statistiques des commandes par client
     */
    public function getStatsByClient() {
        return $this->db->fetchAll(
            "SELECT c.id_client, c.nom, c.prenom, COUNT(*) as count, SUM(co.montant) as total
             FROM commande co
             JOIN client c ON co.id_client = c.id_client
             GROUP BY c.id_client, c.nom, c.prenom
             ORDER BY total DESC
             LIMIT 10"
        );
    }
    
    /**
     * Récupérer les statistiques des commandes par produit
     */
    public function getStatsByProduct() {
        return $this->db->fetchAll(
            "SELECT p.id_produit, p.designation, SUM(cp.quantite) as total_quantity, 
                    SUM(cp.quantite * cp.prix_unitaire) as total_value
             FROM commande_produit cp
             JOIN produit p ON cp.id_produit = p.id_produit
             GROUP BY p.id_produit, p.designation
             ORDER BY total_value DESC
             LIMIT 10"
        );
    }
    
    /**
     * Générer une facture pour une commande
     */
    public function generateInvoice($id) {
        $order = $this->getById($id);
        $products = $this->getProducts($id);
        
        // Ici, vous pourriez générer un PDF ou simplement retourner les données
        return [
            'order' => $order,
            'products' => $products,
            'invoice_number' => generateReference('FAC'),
            'invoice_date' => date('Y-m-d H:i:s'),
            'total' => $order['montant'],
            'tax' => $order['montant'] * 0.2, // TVA à 20%
            'total_with_tax' => $order['montant'] * 1.2
        ];
    }
}
