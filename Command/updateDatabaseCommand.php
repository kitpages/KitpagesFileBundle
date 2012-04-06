<?php
namespace Kitpages\FileBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Kitpages\FileBundle\Entity\FileInterface;
use  Kitpages\FileSystemBundle\ValueObject\AdapterFile;

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
            ->setDescription('update database for kitFileBundle v2.0.0')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getEntityManager('default');

        $fileManager = $this->getContainer()->get('kitpages.file.manager');
        $fileSystemManager = $this->getContainer()->get('kitpages_file_system.filesystem.kitpagesFile');
        $entityClassList = $fileManager->getEntityClassList();


        $dialog = $this->getHelperSet()->get('dialog');
        $dataDir = $dialog->ask(
            $output,
            '<info>Enter the path of data dir?</info> [<comment>'.realpath(__DIR__.'/../../../../app/').'/data/bundle/kitpagesfile</comment>]:',
            realpath(__DIR__.'/../../../../app/').'/data/bundle/kitpagesfile'
        );

        foreach($entityClassList as $entityClass) {
            $fileList = $em->getRepository($entityClass)->findAll();
            foreach($fileList as $file) {
                $type = $file->getType();
                $mimeType = $file->getMimeType();
                $filePath = $dataDir.$this->getPath($file, $fileManager->getEntityFile('', $file));
                if (($type == null || $mimeType == null)
                    && file_exists($filePath)) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $filePath);
                    finfo_close($finfo);
                    $typeList = explode('/', $mimeType);
                    $file->setType($typeList[0]);
                    $file->setMimeType($mimeType);
                    $em->persist($file);
                }

                if (file_exists($filePath)) {
                    $fileTemp = $dataDir.$this->getPath($file, $fileManager->getEntityFile('', $file));

                    $fileSystemManager->moveTempToAdapter(
                        $fileTemp,
                        new AdapterFile($fileManager->getFilePath($file, true), true)
                    );
                }
            }
            $em->flush();
            $output->writeln(sprintf('Modify Database for <comment>%s</comment>', $entityClass));
        }
    }

    public function getPath(FileInterface $file, $entityFile)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        // manage upload

        $prefix = $entityFile['data_dir_prefix'];
        $originalDir = $prefix.'/original/'.$dir;
        $fileName = $originalDir.'/'.$file->getId().'-'.$file->getFilename();
        return $fileName;
    }


}