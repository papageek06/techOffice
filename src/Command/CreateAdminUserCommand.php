<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create-admin',
    description: 'Crée (ou met à jour) un super administrateur',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'login',
                InputArgument::OPTIONAL,
                'Identifiant (email). Si vide, utilise SUPER_ADMIN_EMAIL depuis .env',
                '',
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'Mot de passe. Si vide, utilise SUPER_ADMIN_PASSWORD depuis .env',
                '',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = trim((string) $input->getArgument('login'));
        $plainPassword = trim((string) $input->getArgument('password'));

        // En prod : utiliser les variables d'environnement si les arguments ne sont pas fournis
        if ($login === '' && ($envEmail = $_ENV['SUPER_ADMIN_EMAIL'] ?? null) && $envEmail !== '') {
            $login = $envEmail;
        }
        if ($login === '') {
            $io->error('Indiquez le login (email) en argument ou définissez SUPER_ADMIN_EMAIL dans .env.local');
            return Command::FAILURE;
        }
        if ($plainPassword === '' && ($envPass = $_ENV['SUPER_ADMIN_PASSWORD'] ?? null) && $envPass !== '') {
            $plainPassword = $envPass;
        }
        if ($plainPassword === '') {
            $io->error('Indiquez le mot de passe en argument ou définissez SUPER_ADMIN_PASSWORD dans .env.local');
            return Command::FAILURE;
        }

        $io->title('Création / mise à jour du super administrateur');
        $io->text(sprintf('Login : %s', $login));

        $repo = $this->em->getRepository(User::class);

        /** @var User|null $user */
        $user = $repo->findOneBy(['email' => $login]);
        if (!$user) {
            $user = new User();
            $user->setEmail($login);
            $io->text('Aucun utilisateur trouvé avec ce login, création d’un nouveau compte admin.');
        } else {
            $io->text('Utilisateur existant trouvé, mise à jour du compte admin.');
        }

        // Rôle super admin
        $user->setRoles(['ROLE_ADMIN']);

        // Hash du mot de passe
        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Super admin prêt. Login : %s', $login));

        return Command::SUCCESS;
    }
}

