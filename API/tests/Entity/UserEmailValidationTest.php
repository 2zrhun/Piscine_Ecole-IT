<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class UserEmailValidationTest extends TestCase
{
    private string $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    public function testValidEmails(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.fr',
            'user+tag@example.org',
            'user_name@test-domain.com',
            'user123@domain123.co.uk',
            'firstname.lastname@subdomain.example.com',
            'a@b.co'
        ];

        foreach ($validEmails as $email) {
            $this->assertEquals(
                1, 
                preg_match($this->emailPattern, $email),
                "L'email '{$email}' devrait être valide selon la regex"
            );
        }
    }

    public function testInvalidEmails(): void
    {
        $invalidEmails = [
            'invalid-email',           // pas de @
            '@example.com',           // pas de partie locale
            'test@',                  // pas de domaine
            'test@.com',             // domaine commence par un point
            'test@com',              // pas de TLD
            'test@example.',         // TLD vide
            'test@example.c',        // TLD trop court
            '',                       // email vide
            'test@example',          // pas de TLD
            'test space@example.com', // espace non autorisé
        ];

        foreach ($invalidEmails as $email) {
            $this->assertEquals(
                0, 
                preg_match($this->emailPattern, $email),
                "L'email '{$email}' devrait être invalide selon la regex"
            );
        }
    }

    public function testEmailRegexSpecifically(): void
    {
        // Test spécifiquement notre regex
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        
        // Emails qui doivent passer
        $validEmails = [
            'test@example.com',
            'user.name@domain.fr',
            'user+tag@example.org',
            'user_name@test-domain.com',
            'user123@domain123.co.uk'
        ];

        foreach ($validEmails as $email) {
            $this->assertEquals(
                1, 
                preg_match($pattern, $email),
                "L'email '{$email}' devrait matcher la regex mais ne le fait pas"
            );
        }

        // Emails qui ne doivent pas passer
        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'test@',
            'test@example.c',
            'test@example'
        ];

        foreach ($invalidEmails as $email) {
            $this->assertEquals(
                0, 
                preg_match($pattern, $email),
                "L'email '{$email}' ne devrait pas matcher la regex mais le fait"
            );
        }
    }
}