<?php

namespace App\Helpers;

class TitleHelper
{
    /**
     * Extract grade from the job title.
     *
     * @param string $title
     * @return int
     */
    public static function getGradeFromTitle(string $title): int
    {
        $jobTitle = trim($title);

        // Match specific job titles to predefined grades
        if (preg_match('/\bMenteri Pengangkutan\b/i', $jobTitle)) {
            return 99;
        } elseif (preg_match('/\bTimbalan Menteri\b/i', $jobTitle)) {
            return 97;
        } elseif (preg_match('/\bTurus I\b/i', $jobTitle)) {
            return 95;
        } elseif (preg_match('/\bTurus II\b/i', $jobTitle)) {
            return 94;
        } elseif (preg_match('/\bTurus III\b/i', $jobTitle)) {
            return 93;
        } elseif (preg_match('/\bJusa A\b/i', $jobTitle)) {
            return 92;
        } elseif (preg_match('/\bJusa B\b/i', $jobTitle)) {
            return 91;
        } elseif (preg_match('/\bJusa C\b/i', $jobTitle)) {
            return 90;
        } elseif (preg_match('/\b(MySTEP|PSH|MRL)\b/i', $jobTitle)) {
            return 2;
        } elseif (preg_match('/\b(Pembantu Tadbir \(Kontrak\)|Pembantu Khas)\b/i', $jobTitle)) {
            return 2;
        } elseif (preg_match('/\b(Praktikal|Protege|Pemandu Kenderaan Projek)\b/i', $jobTitle)) {
            return 1;
        } elseif (preg_match('/\bPengawal Pengiring\b/i', $jobTitle)) {
            return 4;
        } elseif (preg_match('/\bDeputy Head\b/i', $jobTitle)) {
            return 48;
        } else {
            // Extract numeric grade from titles like "FA32"
            $matches = [];
            if (preg_match('/(\d+)/', $jobTitle, $matches)) {
                return (int)$matches[1];
            }
        }

        return 0; // Default grade if no match
    }

    /**
     * Determine service group based on the job title.
     *
     * @param string $title
     * @return int
     */
    public static function getServiceGroupFromTitle(string $title): int
    {
        $jobTitle = trim($title);

        // Match specific job titles to predefined service groups
        if (preg_match('/\b(Menteri Pengangkutan|Timbalan Menteri|Turus|JUSA)\b/i', $jobTitle)) {
            return 1; // Top-tier service group
        } elseif (preg_match('/\b(N\d+|Kontrak|PSH|Praktikal|Pengendali|Pembantu Tadbir|Pemandu|Protege|Felo|NRCOE|MRL)\b/i', $jobTitle)) {
            return 3; // Contract or support staff
        } elseif (preg_match('/\bPerunding\b/i', $jobTitle)) {
            return 2; // Consultant group
        } else {
            // Determine service group based on extracted grade
            $matches = [];
            if (preg_match('/(\d+)/', $jobTitle, $matches)) {
                $grade = (int)$matches[1];
                return $grade >= 41 ? 2 : 3; // Professional or support staff
            }
        }

        return 2; // Default to professional group
    }
}
