<?php

namespace AppBundle\Services;

use Psr\Log\LoggerInterface;

class MailManager
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MailManager constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    /**
     * @param string $emailAdress
     * @param string $name
     * @param string $surname
     * @return int
     */
    public function sendMail($emailAdress, $name, $surname)
    {
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject("_____SUBJECT______")
                ->setFrom("donotreply@skillz.zitec.ro")
                ->setTo($emailAdress)
                ->setBody(
                    $this->twig->render(
                        'email_template.html.twig',
                        array(
                            'name' => $name,
                            'surname' => $surname,
                        )
                    ),
                    'text/html'
                );
            $response = $this->mailer->send($message);
            $this->logger->info(
                "Email was send to: " . $emailAdress . \date(" Y-m-d H:i")
            );
            return $response;

        } catch (\Exception $exception) {
            $this->logger->error(
                "Email could not be send to: " . $emailAdress . \date(" Y-m-d H:i") . " error: " . $exception->getMessage()
            );
        }
        return 0;
    }
}