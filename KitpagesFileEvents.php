<?php
namespace Kitpages\FileBundle;

final class KitpagesFileEvents
{
    const onFileUpload = 'kitpages.file.on_upload';
    const afterFileUpload = 'kitpages.file.after_upload';
    
    const onFilePublish = 'kitpages.file.on_publish';
    const afterFilePublish = 'kitpages.file.after_publish';
    
    const onFileDelete = 'kitpages.file.on_delete';
    const afterFileDelete = 'kitpages.file.after_delete';    
    
    const onFileUnpublish = 'kitpages.file.on_unpublish';
    const afterFileUnpublish = 'kitpages.file.after_unpublish';        
}
