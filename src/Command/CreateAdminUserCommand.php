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
                'Identifiant (email) de connexion de l’admin',
                'admin@admin',
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'Mot de passe de l’admin',
                'DOM@dom1409',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = (string) $input->getArgument('login');
        $plainPassword = (string) $input->getArgument('password');

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

        $io->success(sprintf(
            'Super admin prêt ! Login: %s | Mot de passe: %s',
            $login,
            $plainPassword,
        ));

        return Command::SUCCESS;
    }
}

