Hugo ROUMAGNE & Nino DIDIER
API d'une liste de puzzle & casses-têtes:
ils sont définis par l'entité produits et ont:
un nom,
un prix,
un niveau de difficulté,
un Type (liste dans une autre entité/table Type),
un nombre de pièces,
un temps de complètion,
un statut (bool).

On compte rajouter à l'API:
Qu'un produit ait une ou plusieurs photo (avec l'entité picture en ManyToOne)
Des propriétés au produit(date de création du casse-tête, origine (code country?))