<?php

namespace App\Support;

final class CoverageFormTranslations
{
    /**
     * @return array{en: string, fr: string}
     */
    public static function pair(string $key): array
    {
        return self::LABELS[$key] ?? ['en' => $key, 'fr' => $key];
    }

    public static function en(string $key): string
    {
        return self::pair($key)['en'];
    }

    public static function fr(string $key): string
    {
        return self::pair($key)['fr'];
    }

    /**
     * @return list<string>
     */
    public static function supportedLocales(): array
    {
        return ['en', 'fr', 'es'];
    }

    public static function t(string $key, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $pair = self::pair($key);

        if ($locale === 'fr') {
            return $pair['fr'];
        }

        return $pair['en'];
    }

    public static function bilingual(string $key): string
    {
        return self::en($key).' / '.self::fr($key);
    }

    /** @var array<string, array{en: string, fr: string}> */
    public const LABELS = [
        'coverage_information' => ['en' => 'Coverage information', 'fr' => 'Informations de couverture'],
        'family_of_plan' => ['en' => 'Family of :count plan', 'fr' => 'Forfait famille de :count'],
        'family_intro_short' => [
            'en' => 'Covers the primary member, spouse, and children under 18 (full-time students 18–23). Coverage ends at age 23.',
            'fr' => 'Couvre le membre principal, le conjoint et les enfants de moins de 18 ans (étudiants 18–23). Fin à 23 ans.',
        ],
        'family_intro_extended' => [
            'en' => 'Coverage ends at age 23. Step and foster children in a parent-child relationship are also eligible.',
            'fr' => 'La couverture se termine à 23 ans. Les beaux-enfants et enfants adoptifs dans une relation parent-enfant sont aussi admissibles.',
        ],
        'individual_intro' => [
            'en' => 'Tell us who is covered under your membership. This information is used for verification and your digital membership card.',
            'fr' => 'Indiquez qui est couvert par votre adhésion. Ces informations servent à la vérification et à votre carte de membre numérique.',
        ],
        'primary_member' => ['en' => 'Primary member', 'fr' => 'Membre principal'],
        'membership_number' => ['en' => 'Membership', 'fr' => 'Adhésion'],
        'preferred_start_date' => ['en' => 'Preferred coverage start date', 'fr' => 'Date de début de couverture souhaitée'],
        'first_name' => ['en' => 'First name', 'fr' => 'Prénom'],
        'last_name' => ['en' => 'Last name', 'fr' => 'Nom de famille'],
        'date_of_birth' => ['en' => 'Date of birth', 'fr' => 'Date de naissance'],
        'gender' => ['en' => 'Gender', 'fr' => 'Genre'],
        'phone' => ['en' => 'Phone', 'fr' => 'Téléphone'],
        'email' => ['en' => 'Email', 'fr' => 'Courriel'],
        'id_number' => ['en' => 'ID / passport number', 'fr' => 'Numéro de pièce d’identité / passeport'],
        'street' => ['en' => 'Street address', 'fr' => 'Adresse'],
        'street_line2' => ['en' => 'Address line 2', 'fr' => 'Adresse ligne 2'],
        'city' => ['en' => 'City', 'fr' => 'Ville'],
        'state' => ['en' => 'State / province', 'fr' => 'État / province'],
        'zip_code' => ['en' => 'Postal / zip code', 'fr' => 'Code postal'],
        'country' => ['en' => 'Country', 'fr' => 'Pays'],
        'emergency_contact' => ['en' => 'Emergency contact', 'fr' => 'Contact d’urgence'],
        'dependents' => ['en' => 'Dependents', 'fr' => 'Personnes à charge'],
        'dependents_help' => ['en' => 'Add spouse and children covered under this family plan', 'fr' => 'Ajoutez le conjoint et les enfants couverts par ce forfait famille'],
        'dependent_n' => ['en' => 'Dependent', 'fr' => 'Personne à charge'],
        'relationship' => ['en' => 'Relationship', 'fr' => 'Lien de parenté'],
        'add_more' => ['en' => '+ Add more', 'fr' => '+ Ajouter'],
        'remove' => ['en' => 'Remove', 'fr' => 'Retirer'],
        'notes' => ['en' => 'Notes', 'fr' => 'Notes'],
        'notes_placeholder' => [
            'en' => 'Additional notes about household members',
            'fr' => 'Notes supplémentaires sur les membres du foyer',
        ],
        'at_least_one_dependent' => ['en' => 'At least one dependent', 'fr' => 'Au moins une personne à charge'],
        'insurance_section' => ['en' => 'Medical and evacuation insurance information', 'fr' => 'Informations sur l’assurance médicale et d’évacuation'],
        'insurance_section_help' => ['en' => 'Must have insurance valid outside of the USA', 'fr' => 'Assurance valide requise en dehors des États-Unis'],
        'photo_id' => ['en' => 'Photo ID / driver’s license', 'fr' => 'Pièce d’identité avec photo / permis de conduire'],
        'passport' => ['en' => 'Passport', 'fr' => 'Passeport'],
        'choose_file' => ['en' => 'Choose file', 'fr' => 'Choisir un fichier'],
        'file_upload_help' => ['en' => 'Accepted formats: JPG, PNG, or PDF. Max 5 MB.', 'fr' => 'Formats acceptés : JPG, PNG ou PDF. Max 5 Mo.'],
        'file_on_file' => ['en' => 'Document on file', 'fr' => 'Document enregistré'],
        'insurance_company' => ['en' => 'Insurance company name', 'fr' => 'Nom de la compagnie d’assurance'],
        'policy_number' => ['en' => 'Policy #', 'fr' => 'No de police'],
        'policy_start' => ['en' => 'Policy effective start', 'fr' => 'Début de la police'],
        'policy_end' => ['en' => 'Policy effective end', 'fr' => 'Fin de la police'],
        'member_id' => ['en' => 'ID / member number', 'fr' => 'ID / numéro de membre'],
        'policy_holder_name' => ['en' => 'Policy holder name', 'fr' => 'Nom du titulaire de la police'],
        'policy_holder_relationship' => ['en' => 'Relationship of policy holder', 'fr' => 'Lien avec le titulaire'],
        'beneficiary_name' => ['en' => 'Primary beneficiary (full name)', 'fr' => 'Bénéficiaire principal (nom complet)'],
        'beneficiary_relationship' => ['en' => 'Relationship to beneficiary', 'fr' => 'Lien avec le bénéficiaire'],
        'provider_phone' => ['en' => 'Insurance provider phone', 'fr' => 'Téléphone de l’assureur'],
        'plan_type' => ['en' => 'Plan type / level', 'fr' => 'Type / niveau de plan'],
        'medevac_benefit' => ['en' => 'Max medevac benefit (USD)', 'fr' => 'Prestation max. évacuation médicale (USD)'],
        'medevac_policy' => ['en' => 'Medevac policy number', 'fr' => 'No de police d’évacuation médicale'],
        'medical_section' => ['en' => 'Medical history / medications / conditions', 'fr' => 'Antécédents médicaux / médicaments / conditions'],
        'blood_type' => ['en' => 'Blood type', 'fr' => 'Groupe sanguin'],
        'allergies' => ['en' => 'Allergies', 'fr' => 'Allergies'],
        'allergies_placeholder' => ['en' => 'Enter none if not applicable', 'fr' => 'Indiquez aucune si non applicable'],
        'chronic_conditions' => ['en' => 'Chronic medical conditions / current treatments', 'fr' => 'Conditions médicales chroniques / traitements en cours'],
        'medical_checklist' => ['en' => 'Medical history checklist', 'fr' => 'Liste de vérification des antécédents médicaux'],
        'other_medical' => ['en' => 'Other medical history / additional info', 'fr' => 'Autres antécédents médicaux / informations supplémentaires'],
        'terms_section' => ['en' => 'Terms and conditions', 'fr' => 'Termes et conditions'],
        'terms_body' => [
            'en' => 'See coverage application terms (Parts 1–4).',
            'fr' => 'Voir les termes de la demande de couverture (parties 1 à 4).',
        ],
        'terms_accept' => ['en' => 'I agree to the terms and conditions.', 'fr' => 'J’accepte les termes et conditions.'],
        'submit' => ['en' => 'Submit', 'fr' => 'Soumettre'],
        'save_continue' => ['en' => 'Save & continue', 'fr' => 'Enregistrer et continuer'],
        'coverage_type' => ['en' => 'Coverage type', 'fr' => 'Type de couverture'],
        'assigned_from_plan' => ['en' => 'Assigned from your plan', 'fr' => 'Attribué selon votre forfait'],
        'reminder' => ['en' => 'Reminder', 'fr' => 'Rappel'],
        'reminder_body' => ['en' => 'Please provide your coverage information to access all portal features.', 'fr' => 'Veuillez fournir vos informations de couverture pour accéder à toutes les fonctionnalités du portail.'],
        'profile_complete' => ['en' => 'Profile complete', 'fr' => 'Profil complet'],
        'profile_complete_body' => ['en' => 'Your coverage information is on file.', 'fr' => 'Vos informations de couverture sont enregistrées.'],
        'select' => ['en' => 'Select…', 'fr' => 'Sélectionner…'],
        'gender_female' => ['en' => 'Female', 'fr' => 'Féminin'],
        'gender_male' => ['en' => 'Male', 'fr' => 'Masculin'],
        'gender_other' => ['en' => 'Other', 'fr' => 'Autre'],
        'gender_prefer_not' => ['en' => 'Prefer not to say', 'fr' => 'Je préfère ne pas répondre'],
        'relationship_spouse' => ['en' => 'Spouse', 'fr' => 'Conjoint(e)'],
        'relationship_child' => ['en' => 'Child', 'fr' => 'Enfant'],
        'relationship_step_child' => ['en' => 'Step child', 'fr' => 'Beau-fils / belle-fille'],
        'relationship_foster_child' => ['en' => 'Foster child', 'fr' => 'Enfant en famille d’accueil'],
        'relationship_other' => ['en' => 'Other', 'fr' => 'Autre'],
        'banner_complete' => ['en' => 'Complete your coverage information', 'fr' => 'Complétez vos informations de couverture'],
        'banner_body' => ['en' => 'Please provide your coverage details to access all portal features.', 'fr' => 'Veuillez fournir vos informations de couverture pour accéder à toutes les fonctionnalités du portail.'],
        'banner_still_needed' => ['en' => 'Still needed:', 'fr' => 'Encore requis :'],
        'banner_complete_now' => ['en' => 'Complete now', 'fr' => 'Compléter maintenant'],
        'photo_id_document' => ['en' => 'Photo ID document', 'fr' => 'Pièce d’identité avec photo'],
        'passport_document' => ['en' => 'Passport document', 'fr' => 'Document de passeport'],
        'nationality' => ['en' => 'Nationality', 'fr' => 'Nationalité'],
        'passport_expiry_date' => ['en' => 'Passport expiry date', 'fr' => 'Date d’expiration du passeport'],
        'resident_status' => ['en' => 'Resident status', 'fr' => 'Statut de résident'],
        'identification' => ['en' => 'Identification', 'fr' => 'Identification'],
        'personal_information' => ['en' => 'Personal information', 'fr' => 'Informations personnelles'],
        'physical_metrics' => ['en' => 'Height and weight', 'fr' => 'Taille et poids'],
        'occupation' => ['en' => 'Occupation', 'fr' => 'Profession'],
        'full_address' => ['en' => 'Full address', 'fr' => 'Adresse complète'],
        'measurement_unit' => ['en' => 'Measurement unit', 'fr' => 'Unité de mesure'],
        'height' => ['en' => 'Height', 'fr' => 'Taille'],
        'weight' => ['en' => 'Weight', 'fr' => 'Poids'],
        'unit_metric' => ['en' => 'Metric (cm / kg)', 'fr' => 'Métrique (cm / kg)'],
        'unit_imperial' => ['en' => 'Imperial (ft / lbs)', 'fr' => 'Impérial (pi / lb)'],
        'health_questionnaire_section' => ['en' => 'Health questionnaire', 'fr' => 'Questionnaire de santé'],
        'health_plan_name' => ['en' => 'Health plan name', 'fr' => 'Nom du régime de santé'],
        'health_plan_level' => ['en' => 'Level', 'fr' => 'Niveau'],
        'vip_intro' => [
            'en' => 'Complete this application for your Individual Plan VIP membership. All fields are required unless noted.',
            'fr' => 'Complétez cette demande pour votre adhésion Individual Plan VIP. Tous les champs sont obligatoires sauf indication contraire.',
        ],
        'answer_yes' => ['en' => 'Yes', 'fr' => 'Oui'],
        'answer_no' => ['en' => 'No', 'fr' => 'Non'],
        'resident_citizen' => ['en' => 'Citizen', 'fr' => 'Citoyen'],
        'resident_permanent' => ['en' => 'Permanent resident', 'fr' => 'Résident permanent'],
        'resident_temporary' => ['en' => 'Temporary resident', 'fr' => 'Résident temporaire'],
        'resident_non_resident' => ['en' => 'Non-resident', 'fr' => 'Non-résident'],
        'local_address' => ['en' => 'Local address', 'fr' => 'Adresse locale'],
        'mailing_address' => ['en' => 'Mailing address', 'fr' => 'Adresse postale'],
        'trip_details_section' => ['en' => 'Trip details', 'fr' => 'Détails du voyage'],
        'trips' => ['en' => 'Trips', 'fr' => 'Voyages'],
        'trip_from' => ['en' => 'From', 'fr' => 'De'],
        'trip_price' => ['en' => 'Price', 'fr' => 'Prix'],
        'trip_date' => ['en' => 'Date', 'fr' => 'Date'],
        'trip_total' => ['en' => 'Total', 'fr' => 'Total'],
        'passport_section' => ['en' => 'Passport and visa', 'fr' => 'Passeport et visa'],
        'country_of_citizenship' => ['en' => 'Country of citizenship', 'fr' => 'Pays de citoyenneté'],
        'passport_issued_by' => ['en' => 'Passport issued by', 'fr' => 'Passeport délivré par'],
        'passport_name_notice' => [
            'en' => 'Enter your name exactly as it appears on your passport and official documents.',
            'fr' => 'Entrez votre nom exactement comme il apparaît sur votre passeport et vos documents officiels.',
        ],
        'travel_preferences_section' => ['en' => 'Travel preferences', 'fr' => 'Préférences de voyage'],
        'signature_section' => ['en' => 'Signature', 'fr' => 'Signature'],
        'applicant_signature' => ['en' => 'Applicant signature (type full name)', 'fr' => 'Signature du demandeur (nom complet)'],
        'signature_date' => ['en' => 'Date', 'fr' => 'Date'],
        'continue' => ['en' => 'Continue', 'fr' => 'Continuer'],
        'vip10_intro' => [
            'en' => 'Complete this enrollment form before your 10-day VIP coverage begins.',
            'fr' => 'Complétez ce formulaire d’inscription avant le début de votre couverture VIP de 10 jours.',
        ],
        'individual_plan_intro' => [
            'en' => 'This plan is for an individual member for basic protection. Additional plans are available for families or corporate members. Each VIP plan includes free unlimited tele-medical consultation, prescription delivery and discounts.',
            'fr' => 'Ce plan couvre un membre individuel avec une protection de base. D’autres plans sont disponibles pour les familles et les entreprises.',
        ],
        'primary_care_provider' => ['en' => 'Primary care provider', 'fr' => 'Médecin traitant'],
        'primary_care_provider_prompt' => [
            'en' => 'Do you have a personal doctor you regularly visit?',
            'fr' => 'Avez-vous un médecin personnel que vous consultez régulièrement?',
        ],
        'health_plan_provider' => ['en' => 'Health plan provider', 'fr' => 'Fournisseur du régime de santé'],
        'health_insurer' => ['en' => 'Insurer', 'fr' => 'Assureur'],
        'insurance_record_notice' => [
            'en' => 'The insurance provider and insurer information provided here is for record-keeping purposes only and does not constitute an insurance policy.',
            'fr' => 'Les informations sur le fournisseur et l’assureur servent uniquement à des fins de dossier.',
        ],
        'emergency_contact_gender' => ['en' => 'Emergency contact gender', 'fr' => 'Genre du contact d’urgence'],
    ];

    /** @var array<string, array{en: string, fr: string}> */
    public const MEDICAL_CONDITIONS = [
        'high_blood_pressure' => ['en' => 'High blood pressure', 'fr' => 'Hypertension artérielle'],
        'heart_condition' => ['en' => 'Heart condition / angina', 'fr' => 'Maladie cardiaque / angine'],
        'post_stroke' => ['en' => 'Post stroke / heart attack / TIA', 'fr' => 'AVC / crise cardiaque / AIT'],
        'diabetes' => ['en' => 'Diabetes', 'fr' => 'Diabète'],
        'kidney_disease' => ['en' => 'Kidney disease', 'fr' => 'Maladie rénale'],
        'liver_disease' => ['en' => 'Liver disease', 'fr' => 'Maladie du foie'],
        'implanted_device' => ['en' => 'Implanted device / pacemaker', 'fr' => 'Dispositif implanté / pacemaker'],
        'lung_disease' => ['en' => 'Lung disease', 'fr' => 'Maladie pulmonaire'],
        'psychological' => ['en' => 'Psychological', 'fr' => 'Psychologique'],
        'cancer' => ['en' => 'Cancer', 'fr' => 'Cancer'],
        'heart_disease' => ['en' => 'Heart disease', 'fr' => 'Maladie cardiaque'],
    ];

    /** @var array<string, array{en: string, fr: string}> */
    public const TRAVEL_PREFERENCES = [
        'frequent_flyer' => ['en' => 'Frequent flyer number on file', 'fr' => 'Numéro de voyageur fréquent en dossier'],
        'preferred_airline' => ['en' => 'Preferred airline', 'fr' => 'Compagnie aérienne préférée'],
        'hotel_preference' => ['en' => 'Hotel preference', 'fr' => 'Préférence d’hôtel'],
    ];

    /** @var array<string, array{en: string, fr: string}> */
    public const HEALTH_QUESTIONNAIRE = [
        'healthy_bmi' => [
            'en' => 'Is your height/weight within a healthy range?',
            'fr' => 'Votre taille/poids est-il dans une fourchette saine?',
        ],
        'smokes' => ['en' => 'Do you smoke?', 'fr' => 'Fumez-vous?'],
        'drinks_alcohol' => ['en' => 'Do you drink alcohol?', 'fr' => 'Consommez-vous de l’alcool?'],
        'doctor_visit_5yr' => [
            'en' => 'Have you consulted a doctor in the last 5 years?',
            'fr' => 'Avez-vous consulté un médecin au cours des 5 dernières années?',
        ],
        'hospitalized_5yr' => [
            'en' => 'Have you been hospitalized in the last 5 years?',
            'fr' => 'Avez-vous été hospitalisé au cours des 5 dernières années?',
        ],
        'medication_regular' => [
            'en' => 'Do you take medication on a regular basis?',
            'fr' => 'Prenez-vous des médicaments régulièrement?',
        ],
        'surgery_5yr' => [
            'en' => 'Have you had surgery in the last 5 years?',
            'fr' => 'Avez-vous subi une chirurgie au cours des 5 dernières années?',
        ],
    ];

    /** @var array<string, array{en: string, fr: string}> */
    public const INDIVIDUAL_HEALTH_QUESTIONNAIRE = [
        'medical_conditions' => [
            'en' => 'Do you have any known medical conditions, allergies, or chronic illnesses?',
            'fr' => 'Avez-vous des conditions médicales connues, allergies ou maladies chroniques?',
        ],
        'medications' => [
            'en' => 'Are you currently taking any prescription or over-the-counter medications?',
            'fr' => 'Prenez-vous actuellement des médicaments sur ordonnance ou en vente libre?',
        ],
        'surgeries' => [
            'en' => 'Have you ever undergone any surgeries and medical procedures?',
            'fr' => 'Avez-vous déjà subi des chirurgies ou procédures médicales?',
        ],
        'recent_changes' => [
            'en' => 'Are there any recent changes in your health or new symptoms you have noticed?',
            'fr' => 'Y a-t-il eu des changements récents dans votre santé ou de nouveaux symptômes?',
        ],
        'smoking' => ['en' => 'Do you smoke or use any tobacco products?', 'fr' => 'Fumez-vous ou utilisez-vous du tabac?'],
        'alcohol' => [
            'en' => 'Do you consume alcohol? If yes, how often?',
            'fr' => 'Consommez-vous de l’alcool? Si oui, à quelle fréquence?',
        ],
        'exercise' => [
            'en' => 'Do you engage in regular physical activity or exercise?',
            'fr' => 'Pratiquez-vous une activité physique ou de l’exercice régulièrement?',
        ],
    ];

    /** @var array<string, array{en: string, fr: string}> */
    public const INDIVIDUAL_MEDICAL_CONDITIONS = [
        'high_blood_pressure' => ['en' => 'High blood pressure', 'fr' => 'Hypertension artérielle'],
        'diabetes' => ['en' => 'Diabetes (Type 1 or Type 2)', 'fr' => 'Diabète (type 1 ou 2)'],
        'heart_disease' => ['en' => 'Heart disease or cardiovascular issues', 'fr' => 'Maladie cardiaque ou cardiovasculaire'],
        'asthma_copd' => ['en' => 'Asthma / COPD', 'fr' => 'Asthme / MPOC'],
        'arthritis' => ['en' => 'Arthritis / joint issues', 'fr' => 'Arthrite / problèmes articulaires'],
        'thyroid' => ['en' => 'Thyroid disorders', 'fr' => 'Troubles de la thyroïde'],
        'cancer' => ['en' => 'Cancer (please specify type)', 'fr' => 'Cancer (précisez le type)'],
        'kidney_disease' => ['en' => 'Kidney disease', 'fr' => 'Maladie rénale'],
        'stroke' => ['en' => 'Stroke', 'fr' => 'Accident vasculaire cérébral'],
        'mental_health' => ['en' => 'Depression / anxiety / mental health conditions', 'fr' => 'Dépression / anxiété / santé mentale'],
    ];
}
