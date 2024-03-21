<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Post;
use App\Entity\Thread;
use App\Entity\User;
use App\Form\ForumType;
use App\Form\PostType;
use App\Form\ScheduledThreadType;
use App\Form\ThreadType;
use App\Repository\ForumRepository;
use App\Repository\PostRepository;
use App\Repository\ThreadRepository;
use App\Service\SmsGenerator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\ChatterInterface;
use App\Notification\PublishScheduledThreadsNotification;
use Flasher\Prime\FlasherInterface;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(): Response
    {
        return $this->render('forum/index.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }

    /**
     * @Route("/forumlist", name="list_forum")
     */
    public function listForums(ForumRepository $repository): Response
    {
        $forums = $repository->findAll();


        return $this->render('forum/forumlist.html.twig', ['forums' => $forums]);
    }

    /**
     * @Route("/threadlist/{forumId}", name="list_thread")
     */
    public function listThreadsByForumId(ThreadRepository $repository, ForumRepository $forumRepository, Request $request): Response
    {
        $forumId = $request->get('forumId');

        $forum = $forumRepository->find($forumId);

        $threads = $repository->findRegularThreadsByForumId($forumId);

        // Check if special threads should be displayed
        if ($forumId == 15) {  //$this->isGranted('ROLE_ADMIN')
            // Fetch scheduled threads to publish
            $scheduledThreads = $repository->findScheduledThreadsToPublish();

            // Merge scheduled threads with regular threads
            $threads = array_merge($threads, $scheduledThreads);
        }

        if ($forum !== null) {
            $forumTitle = $forum->getNameForum();
        } else {
            $forumTitle = '';
        }

        return $this->render('forum/threadlist.html.twig', ['forumTitle' => $forumTitle, 'threads' => $threads]);
    }

    /**
     * @Route("/postlist/{threadId}", name="list_post")
     */
    public function listposts(PostRepository $repository, ThreadRepository $threadRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $threadId = $request->get('threadId');
        $thread = $threadRepository->find($threadId);
        $posts = $repository->findPostsByThreadId($threadId);

        $postsQuery = $repository->findPostsByThreadIdQuery($threadId);
        $pagination = $paginator->paginate(
            $postsQuery,
            $request->query->getInt('page', 1), // Get the page parameter from the request, default to 1
            3 // Items per page
        );

        if ($thread !== null) {
            $threadTitle = $thread->getTitleThread();
        } else {
            $threadTitle = '';
        }



        return $this->render('forum/postlist.html.twig', ['threadTitle' => $threadTitle, 'posts' => $posts, 'threadId' => $threadId, 'pagination' => $pagination]);
    }

    #[Route('/addthreadbyform', name: 'addthreadbyform')]
    public function addthreadbyform(Request $request, ManagerRegistry $managerRegistry, ValidatorInterface $validator, FlashyNotifier $flashy)
    {
        $thread = new Thread();

        $thread->setIsSpecial('no');

        $thread->setPublished('yes');

        $form = $this->createForm(ThreadType::class, $thread);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $managerRegistry->getManager();

            // Retrieve the forumId from the request
            $forumId = $request->request->get('thread')['forum'];

            // Find the Forum entity by its ID
            $forum = $em->getRepository(Forum::class)->find($forumId);

            // Set the forum for the thread
            $thread->setForum($forum);

            $em->persist($thread);
            $em->flush();

            //$flashy->success('Thread created successfully.');
            flash()->addSuccess('"Your thread has been successfully added!"');

            return $this->redirectToRoute("list_thread", ['forumId' => $forumId]);
        }

        // Handle form validation errors
        $errors = [];
        if ($form->isSubmitted()) {
            $violations = $validator->validate($thread);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }

        return $this->render('forum/addThreadByForm.html.twig', [
            'threadForm' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    #[Route('/addpostbyform/{threadId}', name: 'addpostbyform')]
    public function addPostbyform($threadId, Request $request, ManagerRegistry $managerRegistry, ValidatorInterface $validator)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate the form and the date format simultaneously
            if ($form->isValid()) {
                $em = $managerRegistry->getManager();
                $thread = $em->getRepository(Thread::class)->find($threadId);
                $post->setThread($thread);

                $em->persist($post);
                $em->flush();

                flash()->addSuccess('"Your post has been successfully added!"');

                return $this->redirectToRoute("list_post", ['threadId' => $threadId]);
            } else {
                // Validate the date format if the form is submitted but not valid
                $creationDatePost = $form->get('creationDatePost')->getData();

                if (!$this->isValidDateTime($creationDatePost)) {
                    $form->get('creationDatePost')->addError(new FormError('Invalid date format.'));
                }
            }
        }

        return $this->render('forum/addPostByForm.html.twig', [
            'postForm' => $form->createView(),
        ]);
    }
    // Custom method to validate the date format
    private function isValidDateTime(string $dateTime): bool
    {
        $format = 'Y-m-d';
        $dateTimeObject = \DateTime::createFromFormat($format, $dateTime);
        return $dateTimeObject !== false && !array_sum($dateTimeObject::getLastErrors());
    }

    #[Route('/forum/back', name: 'forum_back')]
    public function backOfficeForum(
        ForumRepository $forumRepository,
        ThreadRepository $threadRepository,
        PostRepository $postRepository
    ): Response {
        $forums = $forumRepository->findAll();
        $threads = $threadRepository->findAll();
        $posts = $postRepository->findAll();

        return $this->render('forum/backOfficeForum.html.twig', [
            'forums' => $forums,
            'threads' => $threads,
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/forum/delete/{forumId}", name="forum_delete")
     */
    public function deleteForum(int $forumId, ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();
        $forum = $em->getRepository(Forum::class)->find($forumId);

        if (!$forum) {
            throw $this->createNotFoundException('Forum not found');
        }

        // Delete associated threads
        foreach ($forum->getThreads() as $thread) {
            // Delete associated posts
            foreach ($thread->getPosts() as $post) {
                $em->remove($post);
            }
            $em->remove($thread);
        }

        $em->remove($forum);
        $em->flush();

        return $this->redirectToRoute('forum_back');
    }

    /**
     * @Route("/thread/delete/{threadId}", name="thread_delete")
     */
    public function deleteThread(int $threadId, ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();
        $thread = $em->getRepository(Thread::class)->find($threadId);

        if (!$thread) {
            throw $this->createNotFoundException('Thread not found');
        }

        // Delete associated posts
        foreach ($thread->getPosts() as $post) {
            $em->remove($post);
        }

        $em->remove($thread);
        $em->flush();

        return $this->redirectToRoute('forum_back');
    }

    /**
     * @Route("/post/delete/{postId}", name="post_delete")
     */
    public function deletePost(int $postId, ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();
        $post = $em->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('forum_back');
    }

    /**
     * @Route("/forum/edit/{forumId}", name="forum_edit")
     */
    public function editForum(Request $request, int $forumId, ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();
        $forum = $em->getRepository(Forum::class)->find($forumId);

        if (!$forum) {
            throw $this->createNotFoundException('Forum not found');
        }

        // Create a form to edit forum details
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist changes to the database
            $em->flush();

            flash()->addSuccess('"Updated successfully!"');

            // Redirect to the appropriate route after editing
            return $this->redirectToRoute('forum_back');
        }

        return $this->render('forum/editForumByForm.html.twig', [
            'forumForm' => $form->createView(),
            'forumId' => $forumId,
        ]);
    }

    /**
     * @Route("/forum/add", name="forum_add")
     */
    public function addForum(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $forum = new Forum();

        // Create a form to add a new forum
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the new forum to the database
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($forum);
            $entityManager->flush();

            flash()->addSuccess('"Your forum has been successfully added!"');

            // Redirect to the appropriate route after adding the forum
            return $this->redirectToRoute('forum_back');
        }

        return $this->render('forum/addForumByForm.html.twig', [
            'forumForm' => $form->createView(),
        ]);
    }

    #[Route('/addscheduledthreadbyform', name: 'addscheduledthreadbyform')]
    public function addScheduledThreadbyform(Request $request, ManagerRegistry $managerRegistry, ValidatorInterface $validator)
    {
        $defaultForumId = 15;

        $ScheduledThread = new Thread();

        $ScheduledThread->setIsSpecial('yes');

        $ScheduledThread->setPublished('no');

        $form = $this->createForm(ScheduledThreadType::class, $ScheduledThread);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $managerRegistry->getManager();

            // Find the Forum entity by its ID
            $forum = $em->getRepository(Forum::class)->find($defaultForumId);

            // Set the forum for the ScheduledThread
            $ScheduledThread->setForum($forum);

            $em->persist($ScheduledThread);
            $em->flush();

            $this->addFlash('success', 'ScheduledThread created successfully.');

            return $this->redirectToRoute("forum_back", ['defaultForumId' => $defaultForumId]);
        }

        // Handle form validation errors
        $errors = [];
        if ($form->isSubmitted()) {
            $violations = $validator->validate($ScheduledThread);
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }

        return $this->render('forum/addScheduledThreadByForm.html.twig', [
            'ScheduledThreadForm' => $form->createView(),
            'errors' => $errors,
        ]);
    }



    /**
     * @Route("/test-email", name="testemail")
     */
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('zoghlami.dhirar.10@demomailtrap.com') // Replace with your email address
            ->to('zoghlami.dhirar.10@gmail.com') // Replace with recipient's email address
            ->subject('Test Email')
            ->text('This is a test email sent from Symfony.');

        $mailer->send($email);

        return new Response('Test email sent successfully.');
    }

    //La vue du formulaire d'envoie du sms
    #[Route('/sms', name: 'app_home')]
    public function indexsms(): Response
    {
        return $this->render('sms/index.html.twig', ['smsSent' => false]);
    }

    //Gestion de l'envoie du sms
    #[Route('/sendSms', name: 'send_sms', methods: 'POST')]
    public function sendSms(Request $request, SmsGenerator $smsGenerator): Response
    {

        $number = $request->request->get('number');

        $name = $request->request->get('name');

        $text = $request->request->get('text');

        $number_test = $_ENV['twilio_to_number']; // Numéro vérifier par twilio. Un seul numéro autorisé pour la version de test.

        //Appel du service
        $smsGenerator->sendSms($number_test, $name, $text);

        return $this->render('sms/index.html.twig', ['smsSent' => true]);
    }

    #[Route('/publish-threads', name: 'publish_threads')]
    public function publishThreads(SmsGenerator $smsGenerator, ManagerRegistry $managerRegistry): Response
    {

        // Logic to publish special threads
        $em = $managerRegistry->getManager();
        $specialThreads = $em->getRepository(Thread::class)->findBy(['Published' => 'no']);

        // Publish each special thread
        foreach ($specialThreads as $thread) {
            // Update 'is_published' property to true
            $thread->setPublished("yes");

            // Send SMS notification
            //$phoneNumber = 'PHONE_NUMBER_HERE';
            $name = 'ADMIN';
            $text = 'Special thread "' . $thread->getTitleThread() . '" has been published successfully.';
            $number_test = $_ENV['twilio_to_number'];
            $smsGenerator->sendSms($number_test, $name, $text);

            // Persist changes
            $em->persist($thread);
        }

        // Flush changes to the database
        $em->flush();



        return $this->redirectToRoute('forum_back');
    }
}
