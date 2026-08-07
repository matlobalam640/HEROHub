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
    ];
}
