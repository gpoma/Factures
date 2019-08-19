<?php

return [
    'blacklist' => [
        '24eme',
        'recap',
        'temps'
    ],
    'template' => storage_path().'/template/facture.ods',
    'societe' => [
        'name' => '24ème',
        'statut' => 'Société coopérative et participative',
        'statut2' => 'à responsabilité limitée, à capital variable',
        'adresse' => '2 place Sainte-Opportune',
        'cp' => '75001 Paris',
        'contact' => 'equipe@24eme.fr',
        'administratif' => [
            'siren' => '810 720 557',
            'immatriculation' => '810 720 557 R.C.S Paris',
            'tva' => 'FR76 810720557',
            'naf' => '6201Z'
        ],
        'reglement' => [
            'texte' => "Le paiement doit être effectué dans les 30 jours suivant la réception de la facture, au choix par chèque ou virement bancaire.\nTout retard de paiement entraînera des pénalités dues de plein droit, égales au taux d'intérêt appliqué par la Banque Centrale Européenne à son opération de refinancement la plus récente majoré de 10 points de pourcentage. Les pénalités de retard sont exigibles sans qu'un rappel soit nécessaire (Article L441-6 du Code de commerce). Indemnité forfaitaire pour frais de recouvrement de 40 € due en cas de retard de paiement (Art. D441-5 Code commerce)",
            'rib' => '10278 06064 00020261501 56',
            'iban' => 'FR 1027 8060 6400 0202 6150 156',
            'bic' => 'CMCIFR2A'
        ],
    ],
];
