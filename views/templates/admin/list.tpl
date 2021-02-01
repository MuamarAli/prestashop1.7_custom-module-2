<div class="panel panel-default">
    <div class="panel-heading">
        List of client
    </div>

    <div class="panel-body">

        <a
                href="{$link->getAdminLink('AdminModules')}&configure={$moduleName}&addClient"
        >
            <button
                    type="button"
                    class="btn btn-primary"
            >
                Add
            </button>
        </a>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Client name</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>

            <tbody>
                {foreach from=$clients item=client}
                    <tr>
                        <td> {$client.id} </td>
                        <td>
                            <img
                                    src="{$imgPath}{$client.image}"
                                    height="150px"
                                    width="150px"
                            >
                        </td>
                        <td> {$client.name} </td>
                        <td>
                            <a
                                    href="{$link->getAdminLink('AdminModules')}&
                                    configure={$moduleName}&viewClient&id={$client.id}"
                                    class="button"
                            >
                                <button
                                        type="button"
                                        class="btn btn-info btn-block"
                                >
                                    View
                                </button>
                            </a>
                        </td>
                        <td>
                            <a
                                    href="{$link->getAdminLink('AdminModules')}&
                                    configure={$moduleName}&editClient&id={$client.id}"
                                    class="button"
                            >
                                <button
                                        type="button"
                                        class="btn btn-success btn-block"
                                >
                                    Update
                                </button>
                            </a>
                        </td>
                        <td>
                            <a
                                    href="{$link->getAdminLink('AdminModules')}&
                                    configure={$moduleName}&deleteClient&id={$client.id}"
                                    class="button"
                            >
                                <button
                                        type="button"
                                        class="btn btn-danger btn-block"
                                >
                                    Delete
                                </button>
                            </a>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>