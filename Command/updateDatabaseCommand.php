<?php
namespace Kitpages\FileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class updateDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kitFile:updateDatabase')
            ->setHelp(<<<EOT
The <info>kitFile:updateDatabase</info> command updates the records in the table kit_file.
EOT
            )
            ->setDescription('update database for kitFileBundle v1.2.0')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getEntityManager('default');

        $fileManager = $this->getContainer()->get('kitpages.file.manager');
        $entityClassList = $fileManager->getEntityClassList();

        $dialog = $this->getHelperSet()->get('dialog');
        $category = $dialog->ask(
            $output,
            '<info>Enter a item_class to your existing files?</info> [<comment></comment>]:',
            ''
        );

        foreach($entityClassList as $entityClass) {
            $fileList = $em->getRepository($entityClass)->findAll();
            foreach($fileList as $file) {
                $type = $file->getType();
                $mimeType = $file->getMimeType();
                $filePath = $fileManager->getOriginalAbsoluteFileName($file);
                if (($type == null || $mimeType == null)
                    && file_exists($filePath)) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $filePath);
                        finfo_close($finfo);
                        $typeList = explode('/', $mimeType);
                        $file->setCategory($category);
                        $file->setType($typeList[0]);
                        $file->setMimeType($mimeType);
                        $em->persist($file);
                }
            }
            $em->flush();
            $output->writeln(sprintf('Modify Database for <comment>%s</comment>', $entityClass));
        }

    }
}