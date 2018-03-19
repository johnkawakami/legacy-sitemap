import React, { Component } from 'react';
import { CSSTransitionGroup } from 'react-transition-group'
import './App.css';

window.apiurl = "http://localhost:8080/ws/";
window.apiurl = "http://riceball.com/legacy-sitemap/ws/";

function postData(url, data) {
  // Default options are marked with *
  return fetch(url, {
    body: JSON.stringify(data), // must match 'Content-Type' header
    cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
    // credentials: 'same-origin', // include, same-origin, *omit
    headers: {
      'user-agent': 'Mozilla/4.0 MDN Example',
      'content-type': 'application/json'
    },
    method: 'POST', // *GET, POST, PUT, DELETE, etc.
    mode: 'cors', // no-cors, cors, *same-origin
    redirect: 'follow', // *manual, follow, error
    referrer: 'no-referrer', // *client, no-referrer
  })
  .then(response => response.json()) // parses response to JSON
}

class Display extends Component 
{
    constructor(props) {
        super(props);
        this.state = { 
            sitemap: [],
            preview: {
                url: ''
            }
        };
        this.refreshData();
    }

    refreshData() {
        fetch(window.apiurl + "sitemap").then( (response) => {
            response.json().then((data) => {
                this.setState( { sitemap: data } );
            });
        });
    }

    closer(refresh) {
        this.setState({ preview: { url: undefined } });
        if (refresh) {
            this.refreshData();
        }
    }

    render() {
        return (
            <div>
                <CSSTransitionGroup transitionName="example"
                                    transitionAppear={true} 
                                    transitionAppearTimeout={1000} 
                                    transitionEnter={true} 
                                    transitionEnterTimeout={2000} 
                                    transitionLeave={true} 
                                    transitionLeaveTimeout={2000}>
                    <PreviewPane key="ppparent" 
                        className=""
                        url={this.state.preview.url} 
                        closer={this.closer.bind(this)} />
                    <TitleList  
                        list={this.state.sitemap} 
                        openPreview={(url) => { this.setState( { preview: { url: url } } ); }} />
                </CSSTransitionGroup>
            </div>
        );
    }
}

class TitleList extends Component 
{
    constructor(props) {
        super(props);
    }

    render() {
        const items = this.props.list.map( (item) => (
            <li key={item.url}
                className='list__item'
                onClick={ (evt) => { this.props.openPreview(item.url); } }>
                {item.title}
            </li>
        ));

        return (
            <ul key="tlul">
                {items}
            </ul>
        );
    }
}

class PreviewPane extends Component {
    constructor(props) {
        super(props);
        this.state = {};
    }
    trashButton() {
        // post to move the to 'trash'
        this.setState({showBusy: true});
        postData(window.apiurl + 'move', {url: this.props.url, state:'trash'})
        .then(() => { this.setState({showBusy: false}); this.props.closer(true)});
    }
    importButton() {
        // post to move the to 'import'
        this.setState({showBusy: true});
        postData(window.apiurl + 'move', {url: this.props.url, state:'import'})
        .then(() => { this.setState({showBusy: false}); this.props.closer(true)});
    }
    retainButton() {
        // post to move the to 'retain'
        this.setState({showBusy: true});
        postData(window.apiurl + 'move', {url: this.props.url, state:'retain'})
        .then(() => { this.setState({showBusy: false}); this.props.closer(true)});
    }
    closeButton() {
        this.setState({showBusy: false});
        this.props.closer(false);
    }
    render() {
        if (this.state.showBusy) {
            return (
                <div key="pp" className='preview-pane fading'>
                    <div key="pppc" className='preview-controls'>
                        <button key="pppcb1" disabled>Trash</button>
                        <button key="pppcb2" disabled>Queue for Import</button>
                        <button key="pppcd3" disabled>Retain and Defer</button>
                        <button key="pppcb4" disabled style={ {float: 'right'} }>Close</button>
                    </div>
                    <div className="preview-busy">
                        Wait for the server...
                    </div>
                </div>
            );
        } else if (this.props.url) {
            return (
                <div key="pp" className='preview-pane'>
                    <div key="pppc" className='preview-controls'>
                        <button key="pppcb1" onClick={this.trashButton.bind(this)}>Trash</button>
                        <button key="pppcb2" onClick={this.importButton.bind(this)}>Queue for Import</button>
                        <button key="pppcd3" onClick={this.retainButton.bind(this)}>Retain and Defer</button>
                        <button key="pppcb4" onClick={this.closeButton.bind(this)} style={ {float: 'right'} }>Close</button>
                    </div>
                    <iframe key="ppcif" title='fhwifbsefbsaifues' className='preview-iframe' src={this.props.url}></iframe>
                </div>
            );
        } else {
            return (<div key="nada"></div>);
        }
    }
}

export default Display;
