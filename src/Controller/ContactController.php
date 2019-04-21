<?php
namespace App\Controller;

use App\Form\Model\ContactModel;
use App\Form\Type\ContactType;
use App\Http\Request;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="contact_")
 */
class ContactController extends Controller
{
    const TO_EMAIL = 'sean@headzoo.io';

    /**
     * @Route("/contact", name="index")
     *
     * @param Request      $request
     *
     * @param Swift_Mailer $mailer
     *
     * @return Response
     */
    public function indexAction(Request $request, Swift_Mailer $mailer)
    {
        $model = new ContactModel();
        $form  = $this->createForm(ContactType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = (new Swift_Message())
                ->setFrom($model->getEmail())
                ->setTo(self::TO_EMAIL)
                ->setSubject('[nsfwdiscordme contact] ' . $model->getSubject())
                ->setBody($model->getMessage());
            $mailer->send($message);

            $this->addFlash('success', 'Thank you! Your message has been sent.');

            return new RedirectResponse($this->generateUrl('contact_index'));
        }

        return $this->render(
            'contact/index.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}
