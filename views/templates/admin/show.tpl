<div>
    <h1>Show client</h1>
    <p>This is the displaying of client information.</p>
    <p><strong>ID:</strong> {$client[0].id} </p>
    <p><strong>Name:</strong> {$client[0].name} </p>
    <p><strong>Image:</strong></p>
    <img
            src="{$imgPath}{$client[0].image}"
            height="150px"
            width="150px"
    >
</div>