import React, { Component } from 'react';
//import { CSSTransitionGroup } from 'react-transition-group'
// import { CSSTransitionGroup } from 'react-transition-group'
import './App.css';

function postData(url, data) {
  // Default options are marked with *
  return fetch(url, {
    body: JSON.stringify(data), // must match 'Content-Type' header
    cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
    // credentials: 'same-origin', // include, same-origin, *omit
    headers: {
      'user-agent': 'Mozilla/4.0 MDN Example',
      'content-type': 'application/json',
      'Authorization': 'bearer ' + window.LEGACY_SITEMAP_API_KEY
    },
    method: 'POST', // *GET, POST, PUT, DELETE, etc.
    mode: 'cors', // no-cors, cors, *same-origin
    redirect: 'follow', // *manual, follow, error
    referrer: 'no-referrer', // *client, no-referrer
  })
  .then(response => response.json()) // parses response to JSON
}

function apiGetSitemap() {
    return fetch( '/legacy-sitemap/api/sitemap',
                {
                    headers: {
                        'X-mode': process.env.NODE_ENV,
                        'Authorization': 'bearer ' + window.LEGACY_SITEMAP_API_KEY
                    }
                }
            )
           .then( response => response.json() );
}
function apiMove(url, state) {
    return postData( '/legacy-sitemap/api/move', {url: url, state:state} );
}
function apiHtmlImportRedirects(text) {
    return postData( '/legacy-sitemap/api/html-import-redirects', {data: text} );
}

class Display extends Component 
{
    constructor(props) {
        super(props);
        this.state = { 
            sitemap: [],
            preview: {
                url: ''
            },
            // fixme - there are too many variables here. It's really a tri-state: not shown, waiting, shown
            showRedirectionDialog: false
        };
        this.refreshData();
    }

    refreshData() {
        apiGetSitemap().then( data => this.setState( { sitemap: data } ) );
    }

    closer(refresh) {
        this.setState({ preview: { url: undefined } });
        if (refresh) {
            this.refreshData();
        }
    }
    enableRedirection() {
        console.log('redirection tool clicked');
        console.log("setting to", !this.state.showRedirectionDialog);
        this.setState({showRedirectionDialog:!this.state.showRedirectionDialog, response:true });
    }

    render() {
        return (
            <div>
                    <BrandingBar key="bbar" enableRedirection={this.enableRedirection.bind(this)} />
                    <PreviewPane key="ppparent" 
                        className=""
                        url={this.state.preview.url} 
                        closer={this.closer.bind(this)} />
                    <RedirectionDialog key="rdialog" show={this.state.showRedirectionDialog} close={() => { this.setState({showRedirectionDialog: false}); }} />
                    <TitleList  
                        list={this.state.sitemap} 
                        openPreview={(url) => { this.setState( { preview: { url: url } } ); }} />
            </div>
        );
    }
}

const TitleList = (props) =>  
( 
    <ul key="tlul" className="title-list">
    {
        props.list ? (
            props.list.map( (item) => (
                <li key={item.url}
                    className='list__item'
                    onClick={ (evt) => { props.openPreview(item.url); } }>
                    {item.title}
                </li>
            )) 
        ) : ("Trying to load the sitemap...")
    }
    </ul>
);

export class PreviewPane extends Component {
    constructor(props) {
        super(props);
        this.state = {};
    }
    trashButton() {
        this.setState({showBusy: true});
        apiMove(this.props.url, 'trash')
        .then(() => { this.setState({showBusy: false}); })
        .then(() => { this.props.closer(true)});
    }
    importButton() {
        this.setState({showBusy: true});
        apiMove(this.props.url, 'import')
        .then(() => { this.setState({showBusy: false}); })
        .then(() => { this.props.closer(true)});
    }
    retainButton() {
        this.setState({showBusy: true});
        apiMove(this.props.url, 'retain')
        .then(() => { this.setState({showBusy: false}); })
        .then(() => { this.props.closer(true)});
    }
    closeButton() {
        this.setState({showBusy: false});
        this.props.closer(false);
    }
    render() {
        if (this.state.showClosing) {
            return (
                    <div key="pp" className='preview-pane'>
                        <div key="pppc" className='preview-controls'>
                            <button key="pppcb1" disabled>Trash</button>
                            <button key="pppcb2" disabled>Queue for Import</button>
                            <button key="pppcd3" disabled>Retain and Defer</button>
                            <button key="pppcb4" className="preview-pane__close" disabled style={ {float: 'right'} }>&times;</button>
                        </div>
                        <div className="preview-busy">
                            Done.
                        </div>
                    </div>
            );
        } else if (this.state.showBusy) {
            return (
                <div key="pp" className='preview-pane'>
                    <div key="pppc" className='preview-controls'>
                        <button key="pppcb1" disabled>Trash</button>
                        <button key="pppcb2" disabled>Queue for Import</button>
                        <button key="pppcd3" disabled>Retain and Defer</button>
                        <button key="pppcb4" className="preview-pane__close" disabled style={ {float: 'right'} }>&times;</button>
                    </div>
                    <div className="preview-busy">
                        Waiting for the server...
                    </div>
                </div>
            );
        } else if (this.props.url) {
            return (
                <div key="pp" className='preview-pane'>
                    <div key="pppc" className='preview-controls'>
                        <button key="pppcb1" 
                            onClick={this.trashButton.bind(this)}>Trash</button>
                        <button key="pppcb2" 
                            onClick={this.importButton.bind(this)}>Queue for Import</button>
                        <button key="pppcd3" 
                            onClick={this.retainButton.bind(this)}>Retain and Defer</button>
                        <button key="pppcb4" className="preview-pane__close"
                            onClick={this.closeButton.bind(this)} 
                            style={ {float: 'right'} }>&times;</button>
                    </div>
                    <iframe key="ppcif" title='fhwifbsefbsaifues' className='preview-iframe' src={this.props.url}></iframe>
                </div>
            );
        } else {
            return (<div key="nada"></div>);
        }
    }
}

export const BrandingBar = (props) => 
{
    return (
        <div className="branding-bar">
            <span className="branding-bar__title">Legacy Sitemaps, Review Tool</span>
            &nbsp;
            <span className="branding-bar__button" href="/" onClick={props.enableRedirection}>Redirection Tool</span>
        </div>
    );
}

class RedirectionDialog extends Component 
{
    constructor(props) {
        super(props);
        this.state = {
            textarea: '',
            response: null
        };
    }

    postData(evt) {
        evt.preventDefault();
        apiHtmlImportRedirects(this.state.textarea)
        .then((response) => this.setState({response: response}));
    }

    updateTextarea(evt) {
        this.setState( { "textarea": evt.target.value } );
    }

    render() {
        if (this.props.show === true) {
            if (this.state.response) {
                return this.renderResponse();
            } else {
                return this.renderForm();
            }
        }
        return null;
    }

    renderForm() {
        return (
            <div key="rdialog-dialog" className="redirection-dialog">
                <div className="redirection-dialog__header">
                    <button className="redirection-dialog__close" onClick={this.props.close}>&times;</button>
                </div>
                <div className="redirection-dialog__layout">
                    <p>After you import the articles, HTML Import will show you a list of "Redirects". 
                       Paste that list here, and the redirects will be installed into the .htaccess file,
                       and the imported HTML files moved to the trash.</p>
                    <form className="redirection-dialog__form">
                    <textarea className="redirection-dialog__textarea" onChange={this.updateTextarea.bind(this)} value={this.state.textarea}></textarea><br />
                <div className="redirection-dialog__form-footer">
                    <button className="redirection-dialog__button" onClick={this.postData.bind(this)}>Process</button>
                </div>
                    </form>
                </div>
            </div>
        );
    }

    renderResponse() {
        var errors = '';
        var successes = '';
        var response = '';
        if ( (this.state.response.errors !== undefined) && (this.state.response.errors.length > 0)) {
            var idx = 0;
            var errorlist = this.state.response.errors.map((file) => {
                idx++;
                return (
                    <span key={idx}>{file}<br /></span>
                );
            });
            errors = (
                <div>
                    <p className="red">Errors:</p>
                    <p>The following files and URLs were not matched to a file you moved.  Edit your 
                       .htaccess file manually to redirect this file.</p>
                    <div className="redirection-dialog__list">
                        {errorlist}
                    </div>
                </div>
            );
        }
        if ( (this.state.response.successes !== undefined) && (this.state.response.successes.length > 0)) {
            var list = this.state.response.successes.map((file) => {
                return (
                    <span key={file.file}><a target="_blank" rel="noopener noreferrer" href={file.url}>{file.file}</a><br /></span>
                );
            });

            successes = (
                <div>
                    <p>The following URLs are now being redirected. Click the blue links to test.</p>
                    <div className="redirection-dialog__list">
                        {list}
                    </div>
                </div>
            );
        }

        if (successes === '' && errors === '') {
            errors = ( <p>Nothing was submitted.</p> );
        }

        response = (
            <div key="rdialog-dialog" className="redirection-dialog">
                <div className="redirection-dialog__header">
                    <button className="redirection-dialog__close" onClick={this.props.close}>&times;</button>
                </div>
                <div className="redirection-dialog__layout">
                    {errors}
                    {successes}
                    <form className="redirection-dialog__form">
                        <div className="redirection-dialog__form-footer">
                            <button className="redirection-dialog__button" onClick={() => {this.setState({response:null})}}>Retry</button>
                        </div>
                    </form>
                </div>
            </div>
        );
        return response;
    }
}

export default Display;
